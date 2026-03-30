<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSchoolClassRequest;
use App\Http\Requests\UpdateSchoolClassRequest;
use App\Models\AcademicYear;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SchoolClassController extends Controller
{
    public function index(): View
    {
        $classes = SchoolClass::with(['academicYear', 'homeroomTeacher'])->latest()->paginate(10);

        return view('classes.index', compact('classes'));
    }

    public function create(): View
    {
        $teachers = User::whereHas('role', fn ($q) => $q->where('code', Role::TEACHER))->orderBy('full_name')->get();
        $academicYears = AcademicYear::orderByDesc('is_active')->orderByDesc('id')->get();

        return view('classes.create', compact('teachers', 'academicYears'));
    }

    public function store(StoreSchoolClassRequest $request): RedirectResponse
    {
        SchoolClass::create($request->validated());

        return redirect()->route('classes.index')->with('success', 'Class created.');
    }

    public function edit(SchoolClass $schoolClass): View
    {
        $teachers = User::whereHas('role', fn ($q) => $q->where('code', Role::TEACHER))->orderBy('full_name')->get();
        $academicYears = AcademicYear::orderByDesc('is_active')->orderByDesc('id')->get();

        return view('classes.edit', compact('schoolClass', 'teachers', 'academicYears'));
    }

    public function update(UpdateSchoolClassRequest $request, SchoolClass $schoolClass): RedirectResponse
    {
        $schoolClass->update($request->validated());

        return redirect()->route('classes.index')->with('success', 'Class updated.');
    }

    public function destroy(SchoolClass $schoolClass): RedirectResponse
    {
        $schoolClass->delete();

        return redirect()->route('classes.index')->with('success', 'Class moved to trash.');
    }
}
