<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuestionImportRequest;
use App\Models\QuestionBank;
use App\Models\QuestionImportLog;
use App\Models\Role;
use App\Models\Subject;
use App\Services\QuestionAccessService;
use App\Services\QuestionImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuestionImportController extends Controller
{
    public function __construct(
        private readonly QuestionAccessService $accessService,
        private readonly QuestionImportService $importService
    ) {
    }

    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = QuestionImportLog::with(['user', 'subject']);

        if ($user->hasRole(Role::TEACHER)) {
            $query->where('user_id', $user->id);
        }

        if ($q = trim((string) $request->string('q'))) {
            $query->where(function ($w) use ($q): void {
                $w->where('file_name', 'like', "%{$q}%")
                    ->orWhereHas('user', fn ($uq) => $uq->where('full_name', 'like', "%{$q}%"))
                    ->orWhereHas('subject', fn ($sq) => $sq->where('name_id', 'like', "%{$q}%"));
            });
        }

        if ($subjectId = $request->integer('subject_id')) {
            $query->where('subject_id', $subjectId);
        }

        if ($importType = $request->string('import_type')->toString()) {
            if (in_array($importType, ['aiken', 'csv'], true)) {
                $query->where('import_type', $importType);
            }
        }

        $logs = $query->latest()->paginate(20)->withQueryString();
        $subjects = $user->hasRole(Role::TEACHER)
            ? Subject::whereIn('id', $this->accessibleBanksForCurrentUser()->pluck('subject_id')->unique()->values())
                ->orderBy('name_id')
                ->get()
            : Subject::where('is_active', true)->orderBy('name_id')->get();

        return view('question-imports.index', compact('logs', 'subjects'));
    }

    public function create(): View
    {
        $this->authorizeManageImport();
        $banks = $this->accessibleBanksForCurrentUser();

        return view('question-imports.create', compact('banks'));
    }

    public function store(StoreQuestionImportRequest $request): RedirectResponse
    {
        $this->authorizeManageImport();

        $data = $request->validated();
        $bank = QuestionBank::query()->findOrFail($data['question_bank_id']);

        abort_unless($this->accessService->canManageBank(auth()->user(), $bank), 403);

        $file = $request->file('file');
        $extension = strtolower((string) $file?->getClientOriginalExtension());

        if ($data['import_type'] === 'aiken' && ! in_array($extension, ['txt', 'aiken'], true)) {
            return back()->withInput()->with('error', 'AIKEN import requires .txt file.');
        }

        if ($data['import_type'] === 'csv' && ! in_array($extension, ['csv', 'txt', 'xlsx'], true)) {
            return back()->withInput()->with('error', 'CSV import supports .csv or .xlsx file.');
        }

        if ($data['import_type'] === 'aiken') {
            $log = $this->importService->importAiken(auth()->user(), $bank, $file);
        } else {
            $log = $this->importService->importCsv(auth()->user(), $bank, $file);
        }

        return redirect()
            ->route('question-imports.index')
            ->with('success', "Import finished: {$log->success_count} success, {$log->failed_count} failed.");
    }

    public function downloadCsvTemplate(): Response
    {
        $headers = [
            'type', 'question_text', 'question_text_en', 'points', 'difficulty',
            'question_image', 'short_answer_key', 'explanation', 'explanation_en',
            'option_a', 'option_b', 'option_c', 'option_d', 'option_e', 'correct_option',
        ];

        $sampleRows = [
            [
                'multiple_choice', '2 + 2 = ?', '2 + 2 = ?', '1', 'easy', 'images/questions/math-1.jpg', '',
                'Basic arithmetic', 'Basic arithmetic', '3', '4', '5', '6', '', 'B',
            ],
            [
                'short_answer', 'Ibu kota Indonesia?', 'Capital city of Indonesia?', '2', 'easy', '/storage/questions/geography-1.png', 'jakarta|dki jakarta',
                '', '', '', '', '', '', '', '',
            ],
            [
                'multiple_response', 'Pilih bilangan genap.', 'Select all even numbers.', '2', 'easy', 'https://example.com/images/even-number.png', '',
                '', '', '1', '2', '3', '4', '5', 'B,D',
            ],
            [
                'essay', 'Jelaskan dampak globalisasi pada pendidikan.', 'Explain globalization impact in education.', '5', 'medium', '', '',
                '', '', '', '', '', '', '', '',
            ],
            [
                'multiple_choice', 'Planet terbesar di tata surya adalah ...', 'The largest planet in our solar system is ...', '1', 'easy', '/storage/questions/planet.jpg', '',
                '', '', 'Mars', 'Venus', 'Jupiter', 'Saturn', '', 'C',
            ],
            [
                'short_answer', 'Hasil dari 10 x 10 adalah ...', 'Result of 10 x 10 is ...', '1', 'easy', 'images/questions/multiply.png', '100|seratus',
                '', '', '', '', '', '', '', '',
            ],
        ];

        $csv = implode(',', $headers)."\n";
        foreach ($sampleRows as $row) {
            $csv .= implode(',', array_map([$this, 'csvEscape'], $row))."\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="question-import-template.csv"',
        ]);
    }

    public function downloadAikenTemplate(): Response
    {
        $content = "2 + 2 = ?\n";
        $content .= "A. 3\n";
        $content .= "B. 4\n";
        $content .= "C. 5\n";
        $content .= "D. 6\n";
        $content .= "IMAGE: images/questions/math-1.jpg\n";
        $content .= "ANSWER: B\n\n";
        $content .= "TYPE: short_answer\n";
        $content .= "Ibu kota Indonesia adalah ...\n";
        $content .= "IMAGE: /storage/questions/geography-1.png\n";
        $content .= "ANSWER: jakarta|dki jakarta\n\n";
        $content .= "TYPE: multiple_response\n";
        $content .= "Pilih bilangan genap berikut.\n";
        $content .= "A. 1\n";
        $content .= "B. 2\n";
        $content .= "C. 3\n";
        $content .= "D. 4\n";
        $content .= "IMAGE: https://example.com/images/even-number.png\n";
        $content .= "ANSWER: B,D\n\n";
        $content .= "TYPE: essay\n";
        $content .= "Jelaskan dampak globalisasi pada pendidikan.\n\n";
        $content .= "Planet terbesar di tata surya adalah ...\n";
        $content .= "A. Mars\n";
        $content .= "B. Venus\n";
        $content .= "C. Jupiter\n";
        $content .= "D. Saturn\n";
        $content .= "IMAGE: /storage/questions/planet.jpg\n";
        $content .= "ANSWER: C\n\n";
        $content .= "TYPE: short_answer\n";
        $content .= "Hasil dari 10 x 10 adalah ...\n";
        $content .= "ANSWER: 100|seratus\n";

        return response($content, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="question-import-template.aiken.txt"',
        ]);
    }

    private function authorizeManageImport(): void
    {
        abort_unless(auth()->user()->hasRole(Role::SUPER_ADMIN, Role::ADMIN, Role::TEACHER), 403);
    }

    private function accessibleBanksForCurrentUser()
    {
        $query = QuestionBank::query()->with('subject')->latest();

        return $this->accessService->scopeAccessibleBanks($query, auth()->user())->get();
    }

    private function csvEscape(string $value): string
    {
        $value = str_replace('"', '""', $value);

        return '"'.$value.'"';
    }
}
