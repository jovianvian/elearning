<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuestionBankRequest;
use App\Http\Requests\UpdateQuestionBankRequest;
use App\Models\QuestionBank;
use App\Models\Role;
use App\Models\Subject;
use App\Services\QuestionAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuestionBankController extends Controller
{
    public function __construct(private readonly QuestionAccessService $accessService)
    {
    }

    public function index(Request $request): View
    {
        $user = auth()->user();

        $query = QuestionBank::query()
            ->with(['subject', 'creator'])
            ->withCount('questions');

        if ($q = trim((string) $request->string('q'))) {
            $query->where(function ($w) use ($q): void {
                $w->where('title', 'like', "%{$q}%")
                    ->orWhereHas('subject', fn ($sq) => $sq->where('name_id', 'like', "%{$q}%"));
            });
        }

        if ($subjectId = $request->integer('subject_id')) {
            $query->where('subject_id', $subjectId);
        }

        if ($visibility = $request->string('visibility')->toString()) {
            if (in_array($visibility, ['subject_shared', 'private'], true)) {
                $query->where('visibility', $visibility);
            }
        }

        $banks = $this->accessService
            ->scopeAccessibleBanks($query, $user)
            ->orderBy('title')
            ->paginate(10)
            ->withQueryString();
        $subjects = $this->availableSubjectsForCurrentUser();

        return view('question-banks.index', compact('banks', 'subjects'));
    }

    public function create(): View
    {
        $this->authorizeManageEntry();

        $subjects = $this->availableSubjectsForCurrentUser();

        return view('question-banks.create', compact('subjects'));
    }

    public function store(StoreQuestionBankRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorizeManageEntry();

        $data = $request->validated();
        abort_unless($this->accessService->canCreateBankForSubject(auth()->user(), (int) $data['subject_id']), 403);

        $questionBank = QuestionBank::create([
            'subject_id' => $data['subject_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'visibility' => $data['visibility'],
            'created_by' => auth()->id(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Question bank created.',
                'data' => $questionBank->load(['subject', 'creator']),
            ]);
        }

        return redirect()->route('question-banks.index')->with('success', 'Question bank created.');
    }

    public function show(QuestionBank $questionBank): View
    {
        abort_unless($this->accessService->canViewBank(auth()->user(), $questionBank), 403);

        $questionBank->load(['subject', 'creator']);
        $questions = $questionBank->questions()->with('options')->latest()->paginate(10);
        $canManage = $this->accessService->canManageBank(auth()->user(), $questionBank);

        return view('question-banks.show', compact('questionBank', 'questions', 'canManage'));
    }

    public function edit(Request $request, QuestionBank $questionBank): View|JsonResponse
    {
        abort_unless($this->accessService->canManageBank(auth()->user(), $questionBank), 403);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'data' => [
                    'id' => $questionBank->id,
                    'subject_id' => $questionBank->subject_id,
                    'title' => $questionBank->title,
                    'description' => $questionBank->description,
                    'visibility' => $questionBank->visibility,
                ],
            ]);
        }

        $subjects = $this->availableSubjectsForCurrentUser();

        return view('question-banks.edit', compact('questionBank', 'subjects'));
    }

    public function update(UpdateQuestionBankRequest $request, QuestionBank $questionBank): RedirectResponse|JsonResponse
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

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Question bank updated.',
                'data' => $questionBank->fresh()->load(['subject', 'creator']),
            ]);
        }

        return redirect()->route('question-banks.index')->with('success', 'Question bank updated.');
    }

    public function destroy(Request $request, QuestionBank $questionBank): RedirectResponse|JsonResponse
    {
        abort_unless($this->accessService->canManageBank(auth()->user(), $questionBank), 403);

        $questionBank->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Question bank moved to trash.',
            ]);
        }

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
