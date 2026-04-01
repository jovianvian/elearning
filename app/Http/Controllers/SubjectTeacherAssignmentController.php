<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubjectTeacherAssignmentRequest;
use App\Http\Requests\UpdateSubjectTeacherAssignmentRequest;
use App\Models\AcademicYear;
use App\Models\Role;
use App\Models\Subject;
use App\Models\SubjectTeacher;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SubjectTeacherAssignmentController extends Controller
{
    public function index(Request $request): View
    {
        $query = SubjectTeacher::query()
            ->with(['subject:id,name_id,code', 'teacher:id,full_name,nip', 'academicYear:id,name']);

        if ($q = trim((string) $request->string('q'))) {
            $query->where(function ($w) use ($q): void {
                $w->whereHas('teacher', function ($tw) use ($q): void {
                    $tw->where('full_name', 'like', "%{$q}%")
                        ->orWhere('nip', 'like', "%{$q}%");
                })->orWhereHas('subject', function ($sw) use ($q): void {
                    $sw->where('name_id', 'like', "%{$q}%")
                        ->orWhere('code', 'like', "%{$q}%");
                });
            });
        }

        if ($subjectId = $request->integer('subject_id')) {
            $query->where('subject_id', $subjectId);
        }

        if ($yearId = $request->integer('academic_year_id')) {
            $query->where('academic_year_id', $yearId);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', (bool) $request->boolean('is_active'));
        }

        $assignments = $query
            ->join('users', 'users.id', '=', 'subject_teachers.teacher_id')
            ->orderBy('users.full_name')
            ->select('subject_teachers.*')
            ->paginate(12)
            ->withQueryString();

        $subjects = Subject::where('is_active', true)->orderBy('name_id')->get();
        $years = AcademicYear::orderByDesc('is_active')->orderByDesc('id')->get();

        return view('assignments.subject-teachers.index', compact('assignments', 'subjects', 'years'));
    }

    public function create(): View
    {
        $subjects = Subject::where('is_active', true)->orderBy('name_id')->get();
        $teachers = User::whereHas('role', fn ($q) => $q->where('code', Role::TEACHER))->orderBy('full_name')->get();
        $years = AcademicYear::orderByDesc('is_active')->orderByDesc('id')->get();

        return view('assignments.subject-teachers.create', compact('subjects', 'teachers', 'years'));
    }

    public function store(StoreSubjectTeacherAssignmentRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $existing = SubjectTeacher::where('teacher_id', $data['teacher_id'])
            ->where('academic_year_id', $data['academic_year_id'])
            ->first();

        if ($existing) {
            throw ValidationException::withMessages(['teacher_id' => 'Teacher can only have one subject in an academic year.']);
        }

        SubjectTeacher::create($data);

        return redirect()->route('assignments.subject-teachers.index')->with('success', 'Teacher subject assignment created.');
    }

    public function edit(SubjectTeacher $subject_teacher): View
    {
        $subjects = Subject::where('is_active', true)->orderBy('name_id')->get();
        $teachers = User::whereHas('role', fn ($q) => $q->where('code', Role::TEACHER))->orderBy('full_name')->get();
        $years = AcademicYear::orderByDesc('is_active')->orderByDesc('id')->get();

        return view('assignments.subject-teachers.edit', compact('subject_teacher', 'subjects', 'teachers', 'years'));
    }

    public function update(UpdateSubjectTeacherAssignmentRequest $request, SubjectTeacher $subject_teacher): RedirectResponse
    {
        $data = $request->validated();

        $existing = SubjectTeacher::where('teacher_id', $data['teacher_id'])
            ->where('academic_year_id', $data['academic_year_id'])
            ->where('id', '!=', $subject_teacher->id)
            ->first();

        if ($existing) {
            throw ValidationException::withMessages(['teacher_id' => 'Teacher can only have one subject in an academic year.']);
        }

        $subject_teacher->update($data);

        return redirect()->route('assignments.subject-teachers.index')->with('success', 'Teacher subject assignment updated.');
    }

    public function destroy(SubjectTeacher $subject_teacher): RedirectResponse
    {
        $subject_teacher->delete();

        return redirect()->route('assignments.subject-teachers.index')->with('success', 'Teacher subject assignment deleted.');
    }
}
