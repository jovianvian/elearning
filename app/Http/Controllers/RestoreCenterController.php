<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Exam;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\RestoreLog;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RestoreCenterController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->hasRole(Role::SUPER_ADMIN), 403);

        $map = $this->entityMap();
        $entity = $request->string('entity')->toString() ?: 'users';
        if (! array_key_exists($entity, $map)) {
            $entity = 'users';
        }

        $modelClass = $map[$entity];
        $items = $modelClass::onlyTrashed()->latest('deleted_at')->paginate(20)->withQueryString();

        return view('monitoring.restore-center', compact('items', 'entity', 'map'));
    }

    public function restore(string $entity, int $id, ActivityLogService $activityLogService): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(Role::SUPER_ADMIN), 403);

        $map = $this->entityMap();
        abort_unless(array_key_exists($entity, $map), 404);

        $modelClass = $map[$entity];
        $item = $modelClass::onlyTrashed()->findOrFail($id);
        $item->restore();

        RestoreLog::create([
            'restored_by' => auth()->id(),
            'entity_type' => $entity,
            'entity_id' => $item->id,
            'restored_at' => now(),
            'note' => 'Restored from restore center',
        ]);

        $activityLogService->log('restored_via_restore_center', $entity, (int) $item->id, null, $item->toArray());

        return back()->with('success', 'Item restored successfully.');
    }

    private function entityMap(): array
    {
        return [
            'users' => User::class,
            'school_classes' => SchoolClass::class,
            'subjects' => Subject::class,
            'courses' => Course::class,
            'question_banks' => QuestionBank::class,
            'questions' => Question::class,
            'exams' => Exam::class,
        ];
    }
}

