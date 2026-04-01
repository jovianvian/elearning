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

        if ($data['import_type'] === 'csv' && ! in_array($extension, ['csv', 'txt'], true)) {
            return back()->withInput()->with('error', 'CSV import requires .csv file.');
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
            'short_answer_key', 'explanation', 'explanation_en',
            'option_a', 'option_b', 'option_c', 'option_d', 'option_e', 'correct_option',
        ];

        $sampleRows = [
            [
                'multiple_choice', '2 + 2 = ?', '2 + 2 = ?', '1', 'easy', '',
                'Basic arithmetic', 'Basic arithmetic', '3', '4', '5', '6', '', 'B',
            ],
            [
                'short_answer', 'Ibu kota Indonesia?', 'Capital city of Indonesia?', '2', 'easy', 'jakarta',
                '', '', '', '', '', '', '', '',
            ],
            [
                'essay', 'Jelaskan dampak globalisasi.', 'Explain the impact of globalization.', '5', 'medium', '',
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
        $content .= "ANSWER: B\n\n";
        $content .= "Ibu kota Indonesia adalah ...\n";
        $content .= "A. Surabaya\n";
        $content .= "B. Medan\n";
        $content .= "C. Jakarta\n";
        $content .= "D. Bandung\n";
        $content .= "ANSWER: C\n";

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
