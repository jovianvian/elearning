<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSchoolClassRequest;
use App\Http\Requests\UpdateSchoolClassRequest;
use App\Models\AcademicYear;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SchoolClassController extends Controller
{
    public function index(Request $request): View
    {
        $query = SchoolClass::with(['academicYear', 'homeroomTeacher']);

        if ($q = trim((string) $request->string('q'))) {
            $query->where(function ($w) use ($q): void {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%");
            });
        }

        if ($yearId = $request->integer('academic_year_id')) {
            $query->where('academic_year_id', $yearId);
        }

        if ($grade = $request->integer('grade_level')) {
            $query->where('grade_level', $grade);
        }

        $classes = $query->orderBy('name')->paginate(10)->withQueryString();
        $teachers = User::whereHas('role', fn ($q) => $q->where('code', Role::TEACHER))->orderBy('full_name')->get();
        $academicYears = AcademicYear::orderByDesc('is_active')->orderByDesc('id')->get();

        return view('classes.index', compact('classes', 'teachers', 'academicYears'));
    }

    public function create(): View
    {
        $teachers = User::whereHas('role', fn ($q) => $q->where('code', Role::TEACHER))->orderBy('full_name')->get();
        $academicYears = AcademicYear::orderByDesc('is_active')->orderByDesc('id')->get();

        return view('classes.create', compact('teachers', 'academicYears'));
    }

    public function store(StoreSchoolClassRequest $request): RedirectResponse|JsonResponse
    {
        $schoolClass = SchoolClass::create($request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Class created.',
                'data' => $schoolClass->load(['academicYear', 'homeroomTeacher']),
            ]);
        }

        return redirect()->route('classes.index')->with('success', 'Class created.');
    }

    public function edit(Request $request, SchoolClass $schoolClass): View|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'data' => [
                    'id' => $schoolClass->id,
                    'name' => $schoolClass->name,
                    'code' => $schoolClass->code,
                    'grade_level' => $schoolClass->grade_level,
                    'academic_year_id' => $schoolClass->academic_year_id,
                    'homeroom_teacher_id' => $schoolClass->homeroom_teacher_id,
                    'is_active' => (bool) $schoolClass->is_active,
                ],
            ]);
        }

        $teachers = User::whereHas('role', fn ($q) => $q->where('code', Role::TEACHER))->orderBy('full_name')->get();
        $academicYears = AcademicYear::orderByDesc('is_active')->orderByDesc('id')->get();

        return view('classes.edit', compact('schoolClass', 'teachers', 'academicYears'));
    }

    public function update(UpdateSchoolClassRequest $request, SchoolClass $schoolClass): RedirectResponse|JsonResponse
    {
        $schoolClass->update($request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Class updated.',
                'data' => $schoolClass->fresh()->load(['academicYear', 'homeroomTeacher']),
            ]);
        }

        return redirect()->route('classes.index')->with('success', 'Class updated.');
    }

    public function destroy(Request $request, SchoolClass $schoolClass): RedirectResponse|JsonResponse
    {
        $schoolClass->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Class moved to trash.',
            ]);
        }

        return redirect()->route('classes.index')->with('success', 'Class moved to trash.');
    }
}
