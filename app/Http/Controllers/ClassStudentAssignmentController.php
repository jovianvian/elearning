<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClassStudentAssignmentRequest;
use App\Http\Requests\UpdateClassStudentAssignmentRequest;
use App\Models\AcademicYear;
use App\Models\ClassStudent;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ClassStudentAssignmentController extends Controller
{
    public function index(): View
    {
        $assignments = ClassStudent::query()
            ->with(['class:id,name', 'student:id,full_name,nis', 'student.role:id,code', 'academicYear:id,name'])
            ->latest()
            ->paginate(12);

        return view('assignments.class-students.index', compact('assignments'));
    }

    public function create(): View
    {
        $classes = SchoolClass::where('is_active', true)->orderBy('name')->get();
        $students = User::whereHas('role', fn ($q) => $q->where('code', Role::STUDENT))->orderBy('full_name')->get();
        $years = AcademicYear::orderByDesc('is_active')->orderByDesc('id')->get();

        return view('assignments.class-students.create', compact('classes', 'students', 'years'));
    }

    public function store(StoreClassStudentAssignmentRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $existing = ClassStudent::where('student_id', $data['student_id'])
            ->where('academic_year_id', $data['academic_year_id'])
            ->first();

        if ($existing) {
            throw ValidationException::withMessages(['student_id' => 'Student already assigned for this academic year.']);
        }

        ClassStudent::create($data);

        User::where('id', $data['student_id'])->update(['school_class_id' => $data['class_id']]);

        return redirect()->route('assignments.class-students.index')->with('success', 'Student class assignment created.');
    }

    public function edit(ClassStudent $class_student): View
    {
        $classes = SchoolClass::where('is_active', true)->orderBy('name')->get();
        $students = User::whereHas('role', fn ($q) => $q->where('code', Role::STUDENT))->orderBy('full_name')->get();
        $years = AcademicYear::orderByDesc('is_active')->orderByDesc('id')->get();

        return view('assignments.class-students.edit', compact('class_student', 'classes', 'students', 'years'));
    }

    public function update(UpdateClassStudentAssignmentRequest $request, ClassStudent $class_student): RedirectResponse
    {
        $class_student->update($request->validated());
        User::where('id', $class_student->student_id)->update(['school_class_id' => $class_student->class_id]);

        return redirect()->route('assignments.class-students.index')->with('success', 'Student class assignment updated.');
    }

    public function destroy(ClassStudent $class_student): RedirectResponse
    {
        $class_student->delete();

        return redirect()->route('assignments.class-students.index')->with('success', 'Student class assignment deleted.');
    }
}
