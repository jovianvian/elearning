<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentBillPaymentRequest;
use App\Http\Requests\StoreStudentBillRequest;
use App\Models\AcademicYear;
use App\Models\Role;
use App\Models\Semester;
use App\Models\StudentBill;
use App\Models\User;
use App\Services\StudentBillService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class StudentBillController extends Controller
{
    public function __construct(private readonly StudentBillService $billService)
    {
    }

    public function index(Request $request): View
    {
        $user = auth()->user();
        abort_unless($user->hasRole(Role::SUPER_ADMIN, Role::ADMIN, Role::PRINCIPAL), 403);

        $query = StudentBill::query()
            ->with(['student.role', 'student.schoolClass', 'academicYear', 'semester'])
            ->withCount(['items', 'items as paid_items_count' => fn ($q) => $q->where('status', 'paid')]);

        if ($q = trim((string) $request->string('q'))) {
            $query->whereHas('student', function ($sq) use ($q): void {
                $sq->where('full_name', 'like', "%{$q}%")
                    ->orWhere('nis', 'like', "%{$q}%");
            });
        }

        if ($classId = $request->integer('class_id')) {
            $query->whereHas('student', fn ($sq) => $sq->where('school_class_id', $classId));
        }

        if ($status = $request->string('status')->toString()) {
            if (in_array($status, ['unpaid', 'partial', 'paid'], true)) {
                $query->where('status', $status);
            }
        }

        if ($academicYearId = $request->integer('academic_year_id')) {
            $query->where('academic_year_id', $academicYearId);
        }

        if ($semesterId = $request->integer('semester_id')) {
            $query->where('semester_id', $semesterId);
        }

        $bills = $query->latest('id')->paginate(12)->withQueryString();
        $students = User::query()
            ->whereHas('role', fn ($rq) => $rq->where('code', Role::STUDENT))
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'nis']);
        $years = AcademicYear::query()->orderByDesc('is_active')->orderByDesc('id')->get();
        $semesters = Semester::query()->orderByDesc('is_active')->orderByDesc('id')->get();
        $classes = \App\Models\SchoolClass::query()->where('is_active', true)->orderBy('name')->get();

        return view('student-bills.index', compact('bills', 'students', 'years', 'semesters', 'classes'));
    }

    public function show(StudentBill $studentBill): View
    {
        $user = auth()->user();
        abort_unless($user->hasRole(Role::SUPER_ADMIN, Role::ADMIN, Role::PRINCIPAL), 403);

        $studentBill->load(['student.schoolClass', 'academicYear', 'semester', 'items']);

        return view('student-bills.show', compact('studentBill'));
    }

    public function generate(StoreStudentBillRequest $request): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user->hasRole(Role::SUPER_ADMIN, Role::ADMIN), 403);

        $data = $request->validated();
        $student = User::query()->with('role')->findOrFail((int) $data['student_id']);
        abort_unless($student->hasRole(Role::STUDENT), 422);

        $semester = Semester::query()->findOrFail((int) $data['semester_id']);

        $bill = $this->billService->generateBill(
            studentId: $student->id,
            academicYearId: (int) $data['academic_year_id'],
            semester: $semester,
            monthlyAmount: (float) $data['monthly_amount']
        );

        return redirect()->route('student-bills.show', $bill)->with('success', __('ui.student_bill_generated'));
    }

    public function storePayment(StoreStudentBillPaymentRequest $request, StudentBill $studentBill): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user->hasRole(Role::SUPER_ADMIN, Role::ADMIN), 403);

        try {
            $this->billService->applyPayment(
                bill: $studentBill,
                monthNumbers: (array) $request->validated('month_numbers'),
                paymentAmount: (float) $request->validated('payment_amount')
            );
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', __('ui.student_bill_payment_recorded'));
    }
}

