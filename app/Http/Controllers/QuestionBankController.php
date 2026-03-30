<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuestionBankRequest;
use App\Http\Requests\UpdateQuestionBankRequest;
use App\Models\QuestionBank;
use App\Models\Role;
use App\Models\Subject;
use App\Services\QuestionAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class QuestionBankController extends Controller
{
    public function __construct(private readonly QuestionAccessService $accessService)
    {
    }

    public function index(): View
    {
        $user = auth()->user();

        $query = QuestionBank::query()
            ->with(['subject', 'creator'])
            ->withCount('questions')
            ->latest();

        $banks = $this->accessService->scopeAccessibleBanks($query, $user)->paginate(10);

        return view('question-banks.index', compact('banks'));
    }

    public function create(): View
    {
        $this->authorizeManageEntry();

        $subjects = $this->availableSubjectsForCurrentUser();

        return view('question-banks.create', compact('subjects'));
    }

    public function store(StoreQuestionBankRequest $request): RedirectResponse
    {
        $this->authorizeManageEntry();

        $data = $request->validated();
        abort_unless($this->accessService->canCreateBankForSubject(auth()->user(), (int) $data['subject_id']), 403);

        QuestionBank::create([
            'subject_id' => $data['subject_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'visibility' => $data['visibility'],
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('question-banks.index')->with('success', 'Question bank created.');
    }

    public function show(QuestionBank $questionBank): View
    {
        abort_unless($this->accessService->canViewBank(auth()->user(), $questionBank), 403);

        $questionBank->load(['subject', 'creator']);
        $questions = $questionBank->questions()->with('options')->latest()->paginate(10);

        return view('question-banks.show', compact('questionBank', 'questions'));
    }

    public function edit(QuestionBank $questionBank): View
    {
        abort_unless($this->accessService->canManageBank(auth()->user(), $questionBank), 403);

        $subjects = $this->availableSubjectsForCurrentUser();

        return view('question-banks.edit', compact('questionBank', 'subjects'));
    }

    public function update(UpdateQuestionBankRequest $request, QuestionBank $questionBank): RedirectResponse
    {
        abort_unless($this->accessService->canManageBank(auth()->user(), $questionBank), 403);

        $data = $request->validated();
        abort_unless($this->accessService->canCreateBankForSubject(auth()->user(), (int) $data['subject_id']), 403);

        $questionBank->update([
            'subject_id' => $data['subject_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'visibility' => $data['visibility'],
        ]);

        return redirect()->route('question-banks.index')->with('success', 'Question bank updated.');
    }

    public function destroy(QuestionBank $questionBank): RedirectResponse
    {
        abort_unless($this->accessService->canManageBank(auth()->user(), $questionBank), 403);

        $questionBank->delete();

        return redirect()->route('question-banks.index')->with('success', 'Question bank moved to trash.');
    }

    private function authorizeManageEntry(): void
    {
        abort_unless(auth()->user()->hasRole(Role::SUPER_ADMIN, Role::ADMIN, Role::TEACHER), 403);
    }

    private function availableSubjectsForCurrentUser()
    {
        $user = auth()->user();

        if ($user->hasRole(Role::SUPER_ADMIN, Role::ADMIN)) {
            return Subject::where('is_active', true)->orderBy('name_id')->get();
        }

        $subjectIds = $this->accessService->teacherSubjectIds($user);

        return Subject::where('is_active', true)->whereIn('id', $subjectIds)->orderBy('name_id')->get();
    }
}

