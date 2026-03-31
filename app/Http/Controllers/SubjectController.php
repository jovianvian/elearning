<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubjectRequest;
use App\Http\Requests\UpdateSubjectRequest;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(): View
    {
        $subjects = Subject::latest()->paginate(10);

        return view('subjects.index', compact('subjects'));
    }

    public function create(): View
    {
        return view('subjects.create');
    }

    public function store(StoreSubjectRequest $request): RedirectResponse|JsonResponse
    {
        $subject = Subject::create($request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Subject created.',
                'data' => $subject,
            ]);
        }

        return redirect()->route('subjects.index')->with('success', 'Subject created.');
    }

    public function edit(Request $request, Subject $subject): View|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['data' => $subject]);
        }

        return view('subjects.edit', compact('subject'));
    }

    public function update(UpdateSubjectRequest $request, Subject $subject): RedirectResponse|JsonResponse
    {
        $subject->update($request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Subject updated.',
                'data' => $subject->fresh(),
            ]);
        }

        return redirect()->route('subjects.index')->with('success', 'Subject updated.');
    }

    public function destroy(Request $request, Subject $subject): RedirectResponse|JsonResponse
    {
        $subject->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Subject moved to trash.']);
        }

        return redirect()->route('subjects.index')->with('success', 'Subject moved to trash.');
    }
}
