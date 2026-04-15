<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLearningMaterialRequest;
use App\Http\Requests\UpdateLearningMaterialRequest;
use App\Models\Course;
use App\Models\LearningMaterial;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class LearningMaterialController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        abort_unless($user->hasRole(Role::SUPER_ADMIN, Role::ADMIN, Role::TEACHER, Role::PRINCIPAL), 403);

        $query = LearningMaterial::query()
            ->with(['course.subject', 'course.schoolClass', 'creator'])
            ->when($user->hasRole(Role::TEACHER), function ($q) use ($user): void {
                $q->whereHas('course.teachers', fn ($sq) => $sq->where('users.id', $user->id));
            });

        if ($q = trim((string) $request->string('q'))) {
            $query->where(function ($w) use ($q): void {
                $w->where('title', 'like', "%{$q}%")
                    ->orWhereHas('course', fn ($cq) => $cq->where('title', 'like', "%{$q}%"));
            });
        }

        if ($courseId = $request->integer('course_id')) {
            $query->where('course_id', $courseId);
        }

        if ($type = $request->string('type')->toString()) {
            if (in_array($type, LearningMaterial::availableTypes(), true)) {
                $query->where('type', $type);
            }
        }

        if ($request->filled('is_published')) {
            $query->where('is_published', (bool) $request->boolean('is_published'));
        }

        $materials = $query
            ->orderBy('course_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        $courses = $this->queryAccessibleCourses($user)->get();
        $canManage = $user->hasRole(Role::SUPER_ADMIN, Role::ADMIN, Role::TEACHER);

        return view('learning-materials.index', compact('materials', 'courses', 'canManage'));
    }

    public function create(): View
    {
        $user = auth()->user();
        abort_unless($user->hasRole(Role::SUPER_ADMIN, Role::ADMIN, Role::TEACHER), 403);

        $courses = $this->queryAccessibleCourses($user)->get();

        return view('learning-materials.create', [
            'courses' => $courses,
            'types' => LearningMaterial::availableTypes(),
        ]);
    }

    public function store(StoreLearningMaterialRequest $request): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user->hasRole(Role::SUPER_ADMIN, Role::ADMIN, Role::TEACHER), 403);

        $data = $request->validated();
        $course = Course::query()->findOrFail((int) $data['course_id']);
        abort_unless($this->canManageCourse($user, $course), 403);

        $filePath = null;
        $fileName = null;
        if ($request->hasFile('upload_file')) {
            $filePath = $request->file('upload_file')->store('learning-materials/files', 'public');
            $fileName = $request->file('upload_file')->getClientOriginalName();
        }

        $isPublished = (bool) ($data['is_published'] ?? false);
        LearningMaterial::query()->create([
            'course_id' => $course->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'content' => $data['content'] ?? null,
            'type' => $data['type'],
            'external_url' => $data['external_url'] ?? null,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_published' => $isPublished,
            'published_at' => $isPublished ? now() : null,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        return redirect()->route('learning-materials.index')->with('success', __('ui.learning_material_created'));
    }

    public function show(LearningMaterial $learningMaterial): View
    {
        $user = auth()->user();
        abort_unless($user->hasRole(Role::SUPER_ADMIN, Role::ADMIN, Role::TEACHER, Role::PRINCIPAL), 403);
        abort_unless($this->canViewManagementMaterial($user, $learningMaterial), 403);

        $learningMaterial->load(['course.subject', 'course.schoolClass', 'creator', 'updater']);

        return view('learning-materials.show', compact('learningMaterial'));
    }

    public function edit(LearningMaterial $learningMaterial): View
    {
        $user = auth()->user();
        abort_unless($user->hasRole(Role::SUPER_ADMIN, Role::ADMIN, Role::TEACHER), 403);
        abort_unless($this->canManageCourse($user, $learningMaterial->course), 403);

        $learningMaterial->load('course');
        $courses = $this->queryAccessibleCourses($user)->get();

        return view('learning-materials.edit', [
            'learningMaterial' => $learningMaterial,
            'courses' => $courses,
            'types' => LearningMaterial::availableTypes(),
        ]);
    }

    public function update(UpdateLearningMaterialRequest $request, LearningMaterial $learningMaterial): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user->hasRole(Role::SUPER_ADMIN, Role::ADMIN, Role::TEACHER), 403);
        abort_unless($this->canManageCourse($user, $learningMaterial->course), 403);

        $data = $request->validated();
        $targetCourse = Course::query()->findOrFail((int) $data['course_id']);
        abort_unless($this->canManageCourse($user, $targetCourse), 403);

        $filePath = $learningMaterial->file_path;
        $fileName = $learningMaterial->file_name;

        if ($request->boolean('remove_file') && $filePath) {
            Storage::disk('public')->delete($filePath);
            $filePath = null;
            $fileName = null;
        }

        if ($request->hasFile('upload_file')) {
            if ($filePath) {
                Storage::disk('public')->delete($filePath);
            }
            $filePath = $request->file('upload_file')->store('learning-materials/files', 'public');
            $fileName = $request->file('upload_file')->getClientOriginalName();
        }

        $isPublished = (bool) ($data['is_published'] ?? false);
        $publishedAt = $learningMaterial->published_at;
        if ($isPublished && ! $publishedAt) {
            $publishedAt = now();
        }
        if (! $isPublished) {
            $publishedAt = null;
        }

        $learningMaterial->update([
            'course_id' => $targetCourse->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'content' => $data['content'] ?? null,
            'type' => $data['type'],
            'external_url' => $data['external_url'] ?? null,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_published' => $isPublished,
            'published_at' => $publishedAt,
            'updated_by' => $user->id,
        ]);

        return redirect()->route('learning-materials.index')->with('success', __('ui.learning_material_updated'));
    }

    public function destroy(LearningMaterial $learningMaterial): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user->hasRole(Role::SUPER_ADMIN, Role::ADMIN, Role::TEACHER), 403);
        abort_unless($this->canManageCourse($user, $learningMaterial->course), 403);

        if ($learningMaterial->file_path) {
            Storage::disk('public')->delete($learningMaterial->file_path);
        }
        $learningMaterial->delete();

        return redirect()->route('learning-materials.index')->with('success', __('ui.learning_material_deleted'));
    }

    public function togglePublish(LearningMaterial $learningMaterial): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user->hasRole(Role::SUPER_ADMIN, Role::ADMIN, Role::TEACHER), 403);
        abort_unless($this->canManageCourse($user, $learningMaterial->course), 403);

        $nextPublished = ! $learningMaterial->is_published;
        $learningMaterial->update([
            'is_published' => $nextPublished,
            'published_at' => $nextPublished ? Carbon::now() : null,
            'updated_by' => $user->id,
        ]);

        return back()->with('success', $nextPublished ? __('ui.learning_material_published') : __('ui.learning_material_unpublished'));
    }

    private function queryAccessibleCourses($user)
    {
        return Course::query()
            ->with(['subject', 'schoolClass'])
            ->when($user->hasRole(Role::TEACHER), function ($q) use ($user): void {
                $q->whereHas('teachers', fn ($sq) => $sq->where('users.id', $user->id));
            })
            ->orderBy('title');
    }

    private function canManageCourse($user, Course $course): bool
    {
        if ($user->hasRole(Role::SUPER_ADMIN, Role::ADMIN)) {
            return true;
        }

        if (! $user->hasRole(Role::TEACHER)) {
            return false;
        }

        return $course->teachers()->where('users.id', $user->id)->exists();
    }

    private function canViewManagementMaterial($user, LearningMaterial $material): bool
    {
        if ($user->hasRole(Role::SUPER_ADMIN, Role::ADMIN, Role::PRINCIPAL)) {
            return true;
        }

        if ($user->hasRole(Role::TEACHER)) {
            return $material->course->teachers()->where('users.id', $user->id)->exists();
        }

        return false;
    }
}

