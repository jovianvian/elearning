<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSemesterRequest;
use App\Http\Requests\UpdateSemesterRequest;
use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SemesterController extends Controller
{
    public function index(): View
    {
        $semesters = Semester::with('academicYear')->latest()->paginate(10);

        return view('semesters.index', compact('semesters'));
    }

    public function create(): View
    {
        $academicYears = AcademicYear::orderByDesc('is_active')->orderByDesc('id')->get();

        return view('semesters.create', compact('academicYears'));
    }

    public function store(StoreSemesterRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (($data['is_active'] ?? false) === true) {
            Semester::query()->update(['is_active' => false]);
        }

        Semester::create($data);

        return redirect()->route('semesters.index')->with('success', 'Semester created.');
    }

    public function edit(Semester $semester): View
    {
        $academicYears = AcademicYear::orderByDesc('is_active')->orderByDesc('id')->get();

        return view('semesters.edit', compact('semester', 'academicYears'));
    }

    public function update(UpdateSemesterRequest $request, Semester $semester): RedirectResponse
    {
        $data = $request->validated();

        if (($data['is_active'] ?? false) === true) {
            Semester::query()->where('id', '!=', $semester->id)->update(['is_active' => false]);
        }

        $semester->update($data);

        return redirect()->route('semesters.index')->with('success', 'Semester updated.');
    }

    public function destroy(Semester $semester): RedirectResponse
    {
        $semester->delete();

        return redirect()->route('semesters.index')->with('success', 'Semester deleted.');
    }
}
