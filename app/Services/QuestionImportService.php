<?php

namespace App\Services;

use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\QuestionImportLog;
use App\Models\QuestionOption;
use App\Models\User;
use App\Support\SimpleXlsxReader;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class QuestionImportService
{
    public function __construct(
        private readonly SimpleXlsxReader $xlsxReader
    ) {
    }

    public function importAiken(User $actor, QuestionBank $bank, UploadedFile $file): QuestionImportLog
    {
        $content = trim((string) file_get_contents($file->getRealPath()));
        $blocks = preg_split("/\n\s*\n/u", str_replace(["\r\n", "\r"], "\n", $content)) ?: [];
        $errors = [];
        $successCount = 0;

        DB::beginTransaction();

        try {
            foreach ($blocks as $index => $block) {
                $rowNumber = $index + 1;
                $block = trim($block);

                if ($block === '') {
                    continue;
                }

                try {
                    $parsed = $this->parseAikenBlock($block);
                    $this->persistAikenQuestion($bank, $actor, $parsed, 'aiken');
                    $successCount++;
                } catch (Throwable $e) {
                    $errors[] = ['row' => $rowNumber, 'error' => $e->getMessage()];
                }
            }

            $log = QuestionImportLog::create([
                'user_id' => $actor->id,
                'subject_id' => $bank->subject_id,
                'import_type' => 'aiken',
                'file_name' => $file->getClientOriginalName(),
                'total_rows' => count($blocks),
                'success_count' => $successCount,
                'failed_count' => count($errors),
                'error_log' => $errors,
            ]);

            DB::commit();

            return $log;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function importCsv(User $actor, QuestionBank $bank, UploadedFile $file): QuestionImportLog
    {
        $rows = $this->readTabularRows($file);
        $errors = [];
        $successCount = 0;

        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;

                try {
                    $this->persistCsvRow($bank, $actor, $row);
                    $successCount++;
                } catch (Throwable $e) {
                    $errors[] = ['row' => $rowNumber, 'error' => $e->getMessage()];
                }
            }

            $log = QuestionImportLog::create([
                'user_id' => $actor->id,
                'subject_id' => $bank->subject_id,
                'import_type' => 'csv',
                'file_name' => $file->getClientOriginalName(),
                'total_rows' => count($rows),
                'success_count' => $successCount,
                'failed_count' => count($errors),
                'error_log' => $errors,
            ]);

            DB::commit();

            return $log;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function parseAikenBlock(string $block): array
    {
        $lines = array_values(array_filter(array_map('trim', explode("\n", $block)), static fn ($line) => $line !== ''));
        $answerLine = null;
        $type = Question::TYPE_MULTIPLE_CHOICE;
        $imagePath = null;
        $options = [];
        $questionLines = [];

        foreach ($lines as $line) {
            if (preg_match('/^TYPE\s*:\s*(.+)$/i', $line, $matches)) {
                $type = $this->normalizeAikenType(trim((string) $matches[1]));
                continue;
            }

            if (preg_match('/^ANSWER\s*:\s*(.+)$/i', $line, $matches)) {
                $answerLine = trim((string) $matches[1]);
                continue;
            }
            if (preg_match('/^IMAGE(?:_URL)?\s*:\s*(.+)$/i', $line, $matches)) {
                $imagePath = $this->normalizeImageReference((string) $matches[1]);
                continue;
            }

            if (preg_match('/^([A-E])[\.\)]\s+(.+)$/i', $line, $matches)) {
                $options[strtoupper($matches[1])] = trim($matches[2]);
                continue;
            }

            $questionLines[] = $line;
        }

        if (empty($questionLines)) {
            throw new RuntimeException('Question text is missing.');
        }

        if ($type === Question::TYPE_ESSAY) {
            return [
                'type' => $type,
                'question_text' => trim(implode(' ', $questionLines)),
                'options' => [],
                'correct_option' => null,
                'correct_options' => [],
                'short_answer_key' => null,
                'question_image_path' => $imagePath,
            ];
        }

        if ($type === Question::TYPE_SHORT_ANSWER) {
            if (! $answerLine) {
                throw new RuntimeException('ANSWER line is required for short_answer.');
            }

            $shortAnswerKey = collect(preg_split('/[|]/', $answerLine) ?: [])
                ->map(fn ($value) => $this->normalizeText((string) $value))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if ($shortAnswerKey === []) {
                throw new RuntimeException('Short answer key is empty.');
            }

            return [
                'type' => $type,
                'question_text' => trim(implode(' ', $questionLines)),
                'options' => [],
                'correct_option' => null,
                'correct_options' => [],
                'short_answer_key' => implode('|', $shortAnswerKey),
                'question_image_path' => $imagePath,
            ];
        }

        if (count($options) < 2) {
            throw new RuntimeException('At least two answer options are required.');
        }

        if (! $answerLine) {
            throw new RuntimeException('ANSWER line is missing.');
        }

        if ($type === Question::TYPE_MULTIPLE_RESPONSE) {
            $correctOptions = collect(preg_split('/[,|]/', strtoupper($answerLine)) ?: [])
                ->map(static fn ($value) => strtoupper(trim((string) $value)))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (count($correctOptions) < 2) {
                throw new RuntimeException('Multi-response requires at least two correct option keys in ANSWER.');
            }

            foreach ($correctOptions as $key) {
                if (! array_key_exists($key, $options)) {
                    throw new RuntimeException('ANSWER key does not match available options.');
                }
            }

            return [
                'type' => $type,
                'question_text' => trim(implode(' ', $questionLines)),
                'options' => $options,
                'correct_option' => null,
                'correct_options' => $correctOptions,
                'short_answer_key' => null,
                'question_image_path' => $imagePath,
            ];
        }

        $correctOption = strtoupper(trim($answerLine));
        if (! array_key_exists($correctOption, $options)) {
            throw new RuntimeException('ANSWER key does not match available options.');
        }

        return [
            'type' => $type,
            'question_text' => trim(implode(' ', $questionLines)),
            'options' => $options,
            'correct_option' => $correctOption,
            'correct_options' => [$correctOption],
            'short_answer_key' => null,
            'question_image_path' => $imagePath,
        ];
    }

    private function normalizeAikenType(string $rawType): string
    {
        $normalized = strtolower(trim($rawType));
        return match ($normalized) {
            'multiple_choice', 'mcq', 'single_choice', 'single_answer' => Question::TYPE_MULTIPLE_CHOICE,
            'multiple_response', 'multi_response', 'multi_select', 'multi_answer' => Question::TYPE_MULTIPLE_RESPONSE,
            'short_answer', 'short', 'isian', 'isian_singkat' => Question::TYPE_SHORT_ANSWER,
            'essay', 'esai' => Question::TYPE_ESSAY,
            default => Question::TYPE_MULTIPLE_CHOICE,
        };
    }

    private function persistAikenQuestion(QuestionBank $bank, User $actor, array $payload, string $importSource): void
    {
        $type = (string) ($payload['type'] ?? Question::TYPE_MULTIPLE_CHOICE);
        $question = Question::create([
            'question_bank_id' => $bank->id,
            'subject_id' => $bank->subject_id,
            'created_by' => $actor->id,
            'type' => $type,
            'question_text' => $payload['question_text'],
            'question_text_en' => null,
            'explanation' => null,
            'explanation_en' => null,
            'points' => 1,
            'difficulty' => 'medium',
            'import_source' => $importSource,
            'short_answer_key' => $type === Question::TYPE_SHORT_ANSWER ? ($payload['short_answer_key'] ?? null) : null,
            'question_image_path' => $this->normalizeImageReference((string) ($payload['question_image_path'] ?? '')),
            'is_active' => true,
        ]);

        if (in_array($type, [Question::TYPE_MULTIPLE_CHOICE, Question::TYPE_MULTIPLE_RESPONSE], true)) {
            $correctOptions = collect((array) ($payload['correct_options'] ?? []))
                ->map(static fn ($value) => strtoupper(trim((string) $value)))
                ->filter()
                ->unique()
                ->values()
                ->all();

            foreach ($payload['options'] as $key => $value) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'option_key' => $key,
                    'option_text' => $value,
                    'option_text_en' => null,
                    'is_correct' => in_array($key, $correctOptions, true),
                ]);
            }
        }
    }

    private function readTabularRows(UploadedFile $file): array
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());
        if ($extension === 'xlsx') {
            return $this->readXlsxRows($file);
        }

        return $this->readCsvRows($file);
    }

    private function readCsvRows(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'rb');

        if (! $handle) {
            throw new RuntimeException('Unable to read uploaded CSV file.');
        }

        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);
            throw new RuntimeException('CSV header is missing.');
        }

        $header = array_map(static fn ($item) => strtolower(trim((string) $item)), $header);

        $requiredHeaders = ['type', 'question_text', 'points', 'difficulty'];
        foreach ($requiredHeaders as $column) {
            if (! in_array($column, $header, true)) {
                fclose($handle);
                throw new RuntimeException("CSV missing required column: {$column}");
            }
        }

        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            if ($this->isCsvRowEmpty($row)) {
                continue;
            }

            $assoc = [];
            foreach ($header as $index => $column) {
                $assoc[$column] = trim((string) ($row[$index] ?? ''));
            }
            $rows[] = $assoc;
        }

        fclose($handle);

        return $rows;
    }

    private function readXlsxRows(UploadedFile $file): array
    {
        $rows = $this->xlsxReader->readFirstSheetRows($file->getRealPath());
        if (count($rows) === 0) {
            throw new RuntimeException('XLSX sheet is empty.');
        }

        $firstRow = $rows[0];
        ksort($firstRow);
        $headerColumns = [];
        foreach ($firstRow as $columnIndex => $label) {
            $normalized = strtolower(trim((string) $label));
            if ($normalized === '') {
                continue;
            }
            $headerColumns[(int) $columnIndex] = $normalized;
        }

        if (count($headerColumns) === 0) {
            throw new RuntimeException('XLSX header is missing.');
        }

        $requiredHeaders = ['type', 'question_text', 'points', 'difficulty'];
        foreach ($requiredHeaders as $column) {
            if (! in_array($column, $headerColumns, true)) {
                throw new RuntimeException("XLSX missing required column: {$column}");
            }
        }

        $result = [];
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (! is_array($row) || count($row) === 0) {
                continue;
            }

            $assoc = [];
            foreach ($headerColumns as $columnIndex => $column) {
                $assoc[$column] = trim((string) ($row[$columnIndex] ?? ''));
            }

            if ($this->isCsvRowEmpty($assoc)) {
                continue;
            }
            $result[] = $assoc;
        }

        return $result;
    }

    private function persistCsvRow(QuestionBank $bank, User $actor, array $row): void
    {
        $type = strtolower($row['type'] ?? '');
        $allowedTypes = [Question::TYPE_MULTIPLE_CHOICE, Question::TYPE_MULTIPLE_RESPONSE, Question::TYPE_SHORT_ANSWER, Question::TYPE_ESSAY];

        if (! in_array($type, $allowedTypes, true)) {
            throw new RuntimeException('Invalid question type.');
        }

        $questionText = trim((string) ($row['question_text'] ?? ''));
        if ($questionText === '') {
            throw new RuntimeException('question_text is required.');
        }

        $points = (float) ($row['points'] ?? 1);
        if ($points <= 0) {
            throw new RuntimeException('points must be greater than zero.');
        }

        $difficulty = trim((string) ($row['difficulty'] ?? 'medium'));
        if (! in_array($difficulty, ['easy', 'medium', 'hard'], true)) {
            throw new RuntimeException('difficulty must be easy, medium, or hard.');
        }

        $shortAnswerKey = $type === Question::TYPE_SHORT_ANSWER
            ? $this->normalizeText($row['short_answer_key'] ?? '')
            : null;

        if ($type === Question::TYPE_SHORT_ANSWER && $shortAnswerKey === null) {
            throw new RuntimeException('short_answer_key is required for short_answer.');
        }

        $objectivePayload = null;
        if (in_array($type, [Question::TYPE_MULTIPLE_CHOICE, Question::TYPE_MULTIPLE_RESPONSE], true)) {
            $objectivePayload = $this->buildCsvObjectivePayload($row, $type);
        }

        $question = Question::create([
            'question_bank_id' => $bank->id,
            'subject_id' => $bank->subject_id,
            'created_by' => $actor->id,
            'type' => $type,
            'question_text' => $questionText,
            'question_text_en' => $this->nullable($row['question_text_en'] ?? null),
            'question_image_path' => $this->normalizeImageReference((string) ($row['question_image'] ?? '')),
            'explanation' => $this->nullable($row['explanation'] ?? null),
            'explanation_en' => $this->nullable($row['explanation_en'] ?? null),
            'points' => $points,
            'difficulty' => $difficulty,
            'import_source' => 'csv',
            'short_answer_key' => $shortAnswerKey,
            'is_active' => true,
        ]);

        if (in_array($type, [Question::TYPE_MULTIPLE_CHOICE, Question::TYPE_MULTIPLE_RESPONSE], true)) {
            $this->persistCsvObjectiveOptions($question, $objectivePayload);
        }
    }

    private function buildCsvObjectivePayload(array $row, string $type): array
    {
        $keys = ['a', 'b', 'c', 'd', 'e'];
        $options = [];

        foreach ($keys as $key) {
            $value = $this->nullable($row['option_'.$key] ?? null);
            if ($value !== null) {
                $optionKey = strtoupper($key);
                $options[$optionKey] = $value;
            }
        }

        if (count($options) < 2) {
            throw new RuntimeException('Multiple choice needs at least two options.');
        }

        if ($type === Question::TYPE_MULTIPLE_CHOICE) {
            $correctOption = strtoupper(trim((string) ($row['correct_option'] ?? '')));
            if ($correctOption === '' || ! isset($options[$correctOption])) {
                throw new RuntimeException('correct_option must match one of provided option keys.');
            }

            return ['options' => $options, 'correct_options' => [$correctOption]];
        }

        $rawCorrect = trim((string) ($row['correct_option'] ?? ''));
        $correctOptions = collect(explode(',', $rawCorrect))
            ->map(static fn ($value) => strtoupper(trim($value)))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (count($correctOptions) < 2) {
            throw new RuntimeException('multiple_response requires at least two correct options in correct_option column (comma separated).');
        }

        foreach ($correctOptions as $correctOption) {
            if (! isset($options[$correctOption])) {
                throw new RuntimeException('correct_option must match provided option keys.');
            }
        }

        return ['options' => $options, 'correct_options' => $correctOptions];
    }

    private function persistCsvObjectiveOptions(Question $question, array $payload): void
    {
        $options = $payload['options'];
        $correctOptions = $payload['correct_options'];

        foreach ($options as $key => $text) {
            QuestionOption::create([
                'question_id' => $question->id,
                'option_key' => $key,
                'option_text' => $text,
                'option_text_en' => null,
                'is_correct' => in_array($key, $correctOptions, true),
            ]);
        }
    }

    private function isCsvRowEmpty(array $row): bool
    {
        foreach ($row as $item) {
            if (trim((string) $item) !== '') {
                return false;
            }
        }

        return true;
    }

    private function nullable(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function normalizeText(string $text): ?string
    {
        $normalized = strtolower(trim(preg_replace('/\s+/u', ' ', $text) ?? ''));

        return $normalized === '' ? null : $normalized;
    }

    private function normalizeImageReference(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $normalized = str_replace('\\', '/', $value);
        $normalized = preg_replace('#^(\./)+#', '', $normalized) ?? $normalized;

        return $normalized;
    }
}
