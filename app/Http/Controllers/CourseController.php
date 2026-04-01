<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\SubjectTeacher;
use App\Models\User;
use App\Services\CourseEnrollmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function __construct(private readonly CourseEnrollmentService $enrollmentService)
    {
    }

    public function index(Request $request): View
    {
        $user = auth()->user();

        $query = Course::with(['subject', 'schoolClass', 'academicYear', 'semester', 'teachers']);

        if ($user->hasRole(Role::TEACHER)) {
            $query->whereHas('teachers', fn ($q) => $q->where('users.id', $user->id));
        }

        if ($user->hasRole(Role::STUDENT)) {
            $query->whereHas('students', fn ($q) => $q->where('users.id', $user->id));
        }

        if ($q = trim((string) $request->string('q'))) {
            $query->where(function ($w) use ($q): void {
                $w->where('title', 'like', "%{$q}%")
                    ->orWhereHas('subject', fn ($sq) => $sq->where('name_id', 'like', "%{$q}%"))
                    ->orWhereHas('schoolClass', fn ($cq) => $cq->where('name', 'like', "%{$q}%"));
            });
        }

        if ($subjectId = $request->integer('subject_id')) {
            $query->where('subject_id', $subjectId);
        }

        if ($classId = $request->integer('class_id')) {
            $query->where('class_id', $classId);
        }

        if ($yearId = $request->integer('academic_year_id')) {
            $query->where('academic_year_id', $yearId);
        }

        if ($semesterId = $request->integer('semester_id')) {
            $query->where('semester_id', $semesterId);
        }

        if ($request->filled('is_published')) {
            $query->where('is_published', (bool) $request->boolean('is_published'));
        }

        $courses = $query->orderBy('title')->paginate(10)->withQueryString();

        $subjects = Subject::where('is_active', true)->orderBy('name_id')->get();
        $classes = SchoolClass::where('is_active', true)->orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('is_active')->orderByDesc('id')->get();
        $semesters = Semester::orderByDesc('is_active')->orderByDesc('id')->get();

        return view('courses.index', compact('courses', 'subjects', 'classes', 'academicYears', 'semesters'));
    }

    public function create(): View
    {
        $this->authorizeCourseManage();

        $subjects = Subject::where('is_active', true)->orderBy('name_id')->get();
        $classes = SchoolClass::where('is_active', true)->orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('is_active')->orderByDesc('id')->get();
        $semesters = Semester::orderByDesc('is_active')->orderByDesc('id')->get();
        $teachers = User::whereHas('role', fn ($q) => $q->where('code', Role::TEACHER))->orderBy('full_name')->get();

        return view('courses.create', compact('subjects', 'classes', 'academicYears', 'semesters', 'teachers'));
    }

    public function store(StoreCourseRequest $request): RedirectResponse
    {
        $this->authorizeCourseManage();

        $data = $request->validated();

        $this->validateCourseScopeUnique($data);
        $this->validateTeachersForSubject($data['teacher_ids'], $data['subject_id'], $data['academic_year_id']);

        $course = Course::create([
            'subject_id' => $data['subject_id'],
            'class_id' => $data['class_id'],
            'academic_year_id' => $data['academic_year_id'],
            'semester_id' => $data['semester_id'],
            'title' => $data['title'],
            'slug' => Str::slug($data['title']).'-'.Str::random(6),
            'description' => $data['description'] ?? null,
            'is_published' => (bool) ($data['is_published'] ?? false),
            'created_by' => auth()->id(),
        ]);

        $this->syncTeachers($course, $data['teacher_ids'], $data['main_teacher_id'] ?? null);
        $this->enrollmentService->syncStudentsFromClass($course->load('schoolClass'));

        return redirect()->route('courses.index')->with('success', 'Course created and students synchronized.');
    }

    public function show(Course $course): View
    {
        $this->authorizeCourseRead($course);

        $course->load(['subject', 'schoolClass', 'academicYear', 'semester', 'teachers', 'students']);

        return view('courses.show', compact('course'));
    }

    public function edit(Course $course): View
    {
        $this->authorizeCourseManage();

        $subjects = Subject::where('is_active', true)->orderBy('name_id')->get();
        $classes = SchoolClass::where('is_active', true)->orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('is_active')->orderByDesc('id')->get();
        $semesters = Semester::orderByDesc('is_active')->orderByDesc('id')->get();
        $teachers = User::whereHas('role', fn ($q) => $q->where('code', Role::TEACHER))->orderBy('full_name')->get();

        $course->load('teachers');

        return view('courses.edit', compact('course', 'subjects', 'classes', 'academicYears', 'semesters', 'teachers'));
    }

    public function update(UpdateCourseRequest $request, Course $course): RedirectResponse
    {
        $this->authorizeCourseManage();

        $data = $request->validated();

        $this->validateCourseScopeUnique($data, $course->id);
        $this->validateTeachersForSubject($data['teacher_ids'], $data['subject_id'], $data['academic_year_id']);

        $course->update([
            'subject_id' => $data['subject_id'],
            'class_id' => $data['class_id'],
            'academic_year_id' => $data['academic_year_id'],
            'semester_id' => $data['semester_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'is_published' => (bool) ($data['is_published'] ?? false),
        ]);

        $this->syncTeachers($course, $data['teacher_ids'], $data['main_teacher_id'] ?? null);
        $this->enrollmentService->syncStudentsFromClass($course->fresh('schoolClass'));

        return redirect()->route('courses.index')->with('success', 'Course updated and students synchronized.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        $this->authorizeCourseManage();

        $course->delete();

        return redirect()->route('courses.index')->with('success', 'Course moved to trash.');
    }

    public function syncStudents(Course $course): RedirectResponse
    {
        $this->authorizeCourseManage();

        $this->enrollmentService->syncStudentsFromClass($course->load('schoolClass'));

        return back()->with('success', 'Course students synchronized from class roster.');
    }

    private function authorizeCourseManage(): void
    {
        abort_unless(auth()->user()->hasRole(Role::SUPER_ADMIN, Role::ADMIN), 403);
    }

    private function authorizeCourseRead(Course $course): void
    {
        $user = auth()->user();

        if ($user->hasRole(Role::SUPER_ADMIN, Role::ADMIN, Role::PRINCIPAL)) {
            return;
        }

        if ($user->hasRole(Role::TEACHER)) {
            $allowed = $course->teachers()->where('users.id', $user->id)->exists();
            abort_unless($allowed, 403);

            return;
        }

        if ($user->hasRole(Role::STUDENT)) {
            $allowed = $course->students()->where('users.id', $user->id)->exists();
            abort_unless($allowed, 403);

            return;
        }

        abort(403);
    }

    private function validateCourseScopeUnique(array $data, ?int $ignoreId = null): void
    {
        $query = Course::where('subject_id', $data['subject_id'])
            ->where('class_id', $data['class_id'])
            ->where('academic_year_id', $data['academic_year_id'])
            ->where('semester_id', $data['semester_id']);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'subject_id' => 'Course scope already exists for selected subject, class, academic year, and semester.',
            ]);
        }
    }

    private function validateTeachersForSubject(array $teacherIds, int $subjectId, int $academicYearId): void
    {
        $teacherIds = array_map('intval', $teacherIds);

        $validTeacherIds = SubjectTeacher::where('subject_id', $subjectId)
            ->where('academic_year_id', $academicYearId)
            ->where('is_active', true)
            ->pluck('teacher_id')
            ->map(static fn ($id) => (int) $id)
            ->all();

        foreach ($teacherIds as $teacherId) {
            if (! in_array($teacherId, $validTeacherIds, true)) {
                throw ValidationException::withMessages([
                    'teacher_ids' => 'Selected teacher is not assigned to this subject for selected academic year.',
                ]);
            }
        }
    }

    private function syncTeachers(Course $course, array $teacherIds, ?int $mainTeacherId = null): void
    {
        $payload = [];
        foreach ($teacherIds as $teacherId) {
            $payload[$teacherId] = ['is_main_teacher' => $mainTeacherId ? ((int) $mainTeacherId === (int) $teacherId) : false];
        }

        $course->teachers()->sync($payload);
    }
}
