<?php

namespace App\Http\Controllers;

use App\Models\LearningMaterial;
use App\Models\Role;
use App\Models\StudentMaterialProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentCourseMaterialController extends Controller
{
    public function index(Request $request): View
    {
        $student = auth()->user();
        abort_unless($student->hasRole(Role::STUDENT), 403);

        $query = LearningMaterial::query()
            ->with(['course.subject', 'course.schoolClass'])
            ->where('is_published', true)
            ->whereHas('course.students', fn ($q) => $q->where('users.id', $student->id));

        if ($q = trim((string) $request->string('q'))) {
            $query->where(function ($w) use ($q): void {
                $w->where('title', 'like', "%{$q}%")
                    ->orWhereHas('course', fn ($cq) => $cq->where('title', 'like', "%{$q}%"));
            });
        }

        if ($courseId = $request->integer('course_id')) {
            $query->where('course_id', $courseId);
        }

        $materials = $query
            ->with(['progresses' => fn ($q) => $q->where('student_id', $student->id)])
            ->orderBy('course_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        $courses = $student->studentCourses()->with('subject', 'schoolClass')->orderBy('title')->get();

        return view('student-materials.index', compact('materials', 'courses'));
    }

    public function show(LearningMaterial $learningMaterial): View
    {
        $student = auth()->user();
        abort_unless($student->hasRole(Role::STUDENT), 403);
        abort_unless($this->canAccessMaterial($student->id, $learningMaterial), 403);

        $learningMaterial->load(['course.subject', 'course.schoolClass']);
        $progress = $this->touchProgress($learningMaterial->id, $student->id);

        return view('student-materials.show', compact('learningMaterial', 'progress'));
    }

    public function complete(LearningMaterial $learningMaterial): RedirectResponse
    {
        $student = auth()->user();
        abort_unless($student->hasRole(Role::STUDENT), 403);
        abort_unless($this->canAccessMaterial($student->id, $learningMaterial), 403);

        $now = now();
        $progress = StudentMaterialProgress::query()->firstOrCreate(
            [
                'learning_material_id' => $learningMaterial->id,
                'student_id' => $student->id,
            ],
            [
                'status' => StudentMaterialProgress::STATUS_NOT_STARTED,
            ]
        );

        $progress->update([
            'status' => StudentMaterialProgress::STATUS_COMPLETED,
            'first_opened_at' => $progress->first_opened_at ?? $now,
            'last_accessed_at' => $now,
            'completed_at' => $now,
        ]);

        return back()->with('success', __('ui.material_marked_completed'));
    }

    private function canAccessMaterial(int $studentId, LearningMaterial $material): bool
    {
        if (! $material->is_published) {
            return false;
        }

        return $material->course->students()->where('users.id', $studentId)->exists();
    }

    private function touchProgress(int $materialId, int $studentId): StudentMaterialProgress
    {
        $now = now();
        $progress = StudentMaterialProgress::query()->firstOrCreate(
            [
                'learning_material_id' => $materialId,
                'student_id' => $studentId,
            ],
            [
                'status' => StudentMaterialProgress::STATUS_NOT_STARTED,
            ]
        );

        $status = $progress->status;
        if ($status === StudentMaterialProgress::STATUS_NOT_STARTED) {
            $status = StudentMaterialProgress::STATUS_IN_PROGRESS;
        }

        $progress->update([
            'status' => $status,
            'first_opened_at' => $progress->first_opened_at ?? $now,
            'last_accessed_at' => $now,
        ]);

        return $progress->fresh();
    }
}

