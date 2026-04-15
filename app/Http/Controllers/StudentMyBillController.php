<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\StudentBill;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentMyBillController extends Controller
{
    public function index(Request $request): View
    {
        $student = auth()->user();
        abort_unless($student->hasRole(Role::STUDENT), 403);

        $query = StudentBill::query()
            ->with(['academicYear', 'semester'])
            ->where('student_id', $student->id);

        if ($status = $request->string('status')->toString()) {
            if (in_array($status, ['unpaid', 'partial', 'paid'], true)) {
                $query->where('status', $status);
            }
        }

        $bills = $query->latest('id')->paginate(12)->withQueryString();

        return view('student-bills.my-index', compact('bills'));
    }

    public function show(StudentBill $studentBill): View
    {
        $student = auth()->user();
        abort_unless($student->hasRole(Role::STUDENT), 403);
        abort_unless((int) $studentBill->student_id === (int) $student->id, 403);

        $studentBill->load(['academicYear', 'semester', 'items']);

        return view('student-bills.my-show', compact('studentBill'));
    }
}

