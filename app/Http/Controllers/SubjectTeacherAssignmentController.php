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
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SubjectTeacherAssignmentController extends Controller
{
    public function index(): View
    {
        $assignments = SubjectTeacher::query()
            ->with(['subject:id,name_id,code', 'teacher:id,full_name,nip', 'academicYear:id,name'])
            ->latest()
            ->paginate(12);

        return view('assignments.subject-teachers.index', compact('assignments'));
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
