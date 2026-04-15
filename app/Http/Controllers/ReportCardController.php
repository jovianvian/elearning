<?php

namespace App\Http\Controllers;

use App\Models\ExamAttempt;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportCardController extends Controller
{
    public function index(Request $request): View
    {
        $query = ExamAttempt::query()
            ->join('users as students', 'students.id', '=', 'exam_attempts.student_id')
            ->join('exams', 'exams.id', '=', 'exam_attempts.exam_id')
            ->join('courses', 'courses.id', '=', 'exams.course_id')
            ->join('school_classes', 'school_classes.id', '=', 'courses.class_id')
            ->whereNotNull('exam_attempts.final_score')
            ->whereIn('exam_attempts.status', [
                ExamAttempt::STATUS_SUBMITTED,
                ExamAttempt::STATUS_AUTO_SUBMITTED,
                ExamAttempt::STATUS_GRADED,
            ]);

        $selectedClassId = (int) $request->integer('class_id');
        if ($selectedClassId > 0) {
            $query->where('courses.class_id', $selectedClassId);
        }

        $search = trim((string) $request->string('q'));
        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('students.full_name', 'like', "%{$search}%")
                    ->orWhere('students.nis', 'like', "%{$search}%");
            });
        }

        $totalStudents = (clone $query)->distinct('exam_attempts.student_id')->count('exam_attempts.student_id');
        $totalAttempts = (clone $query)->count('exam_attempts.id');
        $averageScore = (float) ((clone $query)->avg('exam_attempts.final_score') ?? 0);

        $rows = (clone $query)
            ->select([
                'exam_attempts.student_id',
                'students.full_name',
                'students.nis',
                'school_classes.name as class_name',
                DB::raw('AVG(exam_attempts.final_score) as avg_score'),
                DB::raw('COUNT(exam_attempts.id) as exam_count'),
                DB::raw('MAX(exam_attempts.submitted_at) as last_submitted_at'),
            ])
            ->groupBy('exam_attempts.student_id', 'students.full_name', 'students.nis', 'school_classes.name')
            ->orderByDesc('avg_score')
            ->orderBy('students.full_name')
            ->paginate(20)
            ->withQueryString();

        $classes = DB::table('school_classes')
            ->select('id', 'name')
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        return view('e-rapor.index', [
            'rows' => $rows,
            'classes' => $classes,
            'search' => $search,
            'selectedClassId' => $selectedClassId,
            'totalStudents' => $totalStudents,
            'totalAttempts' => $totalAttempts,
            'averageScore' => $averageScore,
        ]);
    }
}

