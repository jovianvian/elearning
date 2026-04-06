<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAcademicYearRequest;
use App\Http\Requests\UpdateAcademicYearRequest;
use App\Models\AcademicYear;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademicYearController extends Controller
{
    public function index(Request $request): View
    {
        $query = AcademicYear::query();

        if ($q = trim((string) $request->string('q'))) {
            $query->where('name', 'like', "%{$q}%");
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', (bool) $request->boolean('is_active'));
        }

        $academicYears = $query
            ->orderByDesc('is_active')
            ->orderByDesc('start_date')
            ->paginate(10)
            ->withQueryString();

        return view('academic-years.index', compact('academicYears'));
    }

    public function create(): View
    {
        return view('academic-years.create');
    }

    public function store(StoreAcademicYearRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();

        if (($data['is_active'] ?? false) === true) {
            AcademicYear::query()->update(['is_active' => false]);
        }

        $academicYear = AcademicYear::create($data);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Academic year created.',
                'data' => $academicYear,
            ]);
        }

        return redirect()->route('super-admin.academic-years.index')->with('success', 'Academic year created.');
    }

    public function edit(Request $request, AcademicYear $academicYear): View|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'data' => $academicYear,
            ]);
        }

        return view('academic-years.edit', compact('academicYear'));
    }

    public function update(UpdateAcademicYearRequest $request, AcademicYear $academicYear): RedirectResponse|JsonResponse
    {
        $data = $request->validated();

        if (($data['is_active'] ?? false) === true) {
            AcademicYear::query()->where('id', '!=', $academicYear->id)->update(['is_active' => false]);
        }

        $academicYear->update($data);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Academic year updated.',
                'data' => $academicYear->fresh(),
            ]);
        }

        return redirect()->route('super-admin.academic-years.index')->with('success', 'Academic year updated.');
    }

    public function destroy(Request $request, AcademicYear $academicYear): RedirectResponse|JsonResponse
    {
        $academicYear->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Academic year deleted.',
            ]);
        }

        return redirect()->route('super-admin.academic-years.index')->with('success', 'Academic year deleted.');
    }
}
