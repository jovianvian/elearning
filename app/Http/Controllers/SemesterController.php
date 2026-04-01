<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSemesterRequest;
use App\Http\Requests\UpdateSemesterRequest;
use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SemesterController extends Controller
{
    public function index(Request $request): View
    {
        $query = Semester::with('academicYear');

        if ($q = trim((string) $request->string('q'))) {
            $query->where(function ($w) use ($q): void {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%");
            });
        }

        if ($yearId = $request->integer('academic_year_id')) {
            $query->where('academic_year_id', $yearId);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', (bool) $request->boolean('is_active'));
        }

        $semesters = $query
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();
        $academicYears = AcademicYear::orderByDesc('is_active')->orderByDesc('id')->get();

        return view('semesters.index', compact('semesters', 'academicYears'));
    }

    public function create(): View
    {
        $academicYears = AcademicYear::orderByDesc('is_active')->orderByDesc('id')->get();

        return view('semesters.create', compact('academicYears'));
    }

    public function store(StoreSemesterRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();

        if (($data['is_active'] ?? false) === true) {
            Semester::query()->update(['is_active' => false]);
        }

        $semester = Semester::create($data);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Semester created.',
                'data' => $semester->load('academicYear'),
            ]);
        }

        return redirect()->route('super-admin.semesters.index')->with('success', 'Semester created.');
    }

    public function edit(Request $request, Semester $semester): View|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'data' => [
                    'id' => $semester->id,
                    'academic_year_id' => $semester->academic_year_id,
                    'name' => $semester->name,
                    'code' => $semester->code,
                    'is_active' => (bool) $semester->is_active,
                    'start_date' => $semester->start_date?->format('Y-m-d'),
                    'end_date' => $semester->end_date?->format('Y-m-d'),
                ],
            ]);
        }

        $academicYears = AcademicYear::orderByDesc('is_active')->orderByDesc('id')->get();

        return view('semesters.edit', compact('semester', 'academicYears'));
    }

    public function update(UpdateSemesterRequest $request, Semester $semester): RedirectResponse|JsonResponse
    {
        $data = $request->validated();

        if (($data['is_active'] ?? false) === true) {
            Semester::query()->where('id', '!=', $semester->id)->update(['is_active' => false]);
        }

        $semester->update($data);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Semester updated.',
                'data' => $semester->fresh()->load('academicYear'),
            ]);
        }

        return redirect()->route('super-admin.semesters.index')->with('success', 'Semester updated.');
    }

    public function destroy(Request $request, Semester $semester): RedirectResponse|JsonResponse
    {
        $semester->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Semester deleted.']);
        }

        return redirect()->route('super-admin.semesters.index')->with('success', 'Semester deleted.');
    }
}
