<?php

namespace App\Services;

use App\Models\BillItem;
use App\Models\Exam;
use App\Models\Semester;
use App\Models\StudentBill;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StudentBillService
{
    /**
     * @return array{eligible: bool, message: string|null}
     */
    public function checkExamEligibility(User $student, Exam $exam): array
    {
        $requiredMonth = (int) ($exam->required_paid_month ?? 0);
        if ($requiredMonth < 1 || $requiredMonth > 12) {
            return ['eligible' => true, 'message' => null];
        }

        $exam->loadMissing('course');
        $course = $exam->course;
        if (! $course) {
            return ['eligible' => false, 'message' => __('ui.exam_payment_data_missing')];
        }

        $bill = StudentBill::query()
            ->with('items')
            ->where('student_id', $student->id)
            ->where('academic_year_id', $course->academic_year_id)
            ->where('semester_id', $course->semester_id)
            ->first();

        if (! $bill) {
            return [
                'eligible' => false,
                'message' => __('ui.spp_unpaid_until_month', ['month' => $this->monthName($requiredMonth)]),
            ];
        }

        $hasUnpaid = $bill->items
            ->where('month_number', '<=', $requiredMonth)
            ->contains(fn (BillItem $item): bool => $item->status !== BillItem::STATUS_PAID);

        if ($hasUnpaid) {
            return [
                'eligible' => false,
                'message' => __('ui.spp_unpaid_until_month', ['month' => $this->monthName($requiredMonth)]),
            ];
        }

        return ['eligible' => true, 'message' => null];
    }

    public function generateBill(int $studentId, int $academicYearId, Semester $semester, float $monthlyAmount): StudentBill
    {
        if ($monthlyAmount <= 0) {
            throw new RuntimeException('Monthly amount must be greater than zero.');
        }

        return DB::transaction(function () use ($studentId, $academicYearId, $semester, $monthlyAmount): StudentBill {
            $bill = StudentBill::query()->firstOrCreate(
                [
                    'student_id' => $studentId,
                    'academic_year_id' => $academicYearId,
                    'semester_id' => $semester->id,
                ],
                [
                    'total_amount' => 0,
                    'paid_amount' => 0,
                    'status' => StudentBill::STATUS_UNPAID,
                    'generated_at' => now(),
                ]
            );

            $months = $this->monthsForSemester($semester);
            foreach ($months as $month) {
                BillItem::query()->firstOrCreate(
                    [
                        'student_bill_id' => $bill->id,
                        'month_number' => $month,
                    ],
                    [
                        'month_name' => $this->monthName($month),
                        'amount' => $monthlyAmount,
                        'paid_amount' => 0,
                        'status' => BillItem::STATUS_UNPAID,
                    ]
                );
            }

            $this->recalculateBill($bill->refresh()->load('items'));

            return $bill->fresh(['items', 'student', 'academicYear', 'semester']);
        });
    }

    public function applyPayment(StudentBill $bill, array $monthNumbers, float $paymentAmount): StudentBill
    {
        if ($paymentAmount <= 0) {
            throw new RuntimeException('Payment amount must be greater than zero.');
        }

        return DB::transaction(function () use ($bill, $monthNumbers, $paymentAmount): StudentBill {
            $bill->load('items');
            $selectedItems = $bill->items
                ->whereIn('month_number', array_map('intval', $monthNumbers))
                ->sortBy('month_number')
                ->values();

            if ($selectedItems->isEmpty()) {
                throw new RuntimeException('No bill items selected.');
            }

            if ($selectedItems->contains(fn (BillItem $item): bool => $item->status === BillItem::STATUS_PAID)) {
                throw new RuntimeException('Selected month already paid.');
            }

            $outstanding = (float) $selectedItems->sum(fn (BillItem $item): float => max(0, (float) $item->amount - (float) $item->paid_amount));
            if ($paymentAmount > $outstanding) {
                throw new RuntimeException('Payment exceeds outstanding amount.');
            }

            $remaining = $paymentAmount;
            foreach ($selectedItems as $item) {
                if ($remaining <= 0) {
                    break;
                }

                $unpaid = max(0, (float) $item->amount - (float) $item->paid_amount);
                if ($unpaid <= 0) {
                    continue;
                }

                $allocate = min($remaining, $unpaid);
                $newPaid = (float) $item->paid_amount + $allocate;

                $status = BillItem::STATUS_PARTIAL;
                if ($newPaid <= 0) {
                    $status = BillItem::STATUS_UNPAID;
                } elseif ($newPaid >= (float) $item->amount) {
                    $status = BillItem::STATUS_PAID;
                }

                $item->update([
                    'paid_amount' => $newPaid,
                    'status' => $status,
                    'paid_at' => $status === BillItem::STATUS_PAID ? now() : $item->paid_at,
                ]);

                $remaining -= $allocate;
            }

            $this->recalculateBill($bill->refresh()->load('items'));

            return $bill->fresh(['items', 'student', 'academicYear', 'semester']);
        });
    }

    public function recalculateBill(StudentBill $bill): void
    {
        $bill->loadMissing('items');

        $total = (float) $bill->items->sum('amount');
        $paid = (float) $bill->items->sum('paid_amount');

        $status = StudentBill::STATUS_UNPAID;
        if ($paid <= 0) {
            $status = StudentBill::STATUS_UNPAID;
        } elseif ($paid >= $total) {
            $status = StudentBill::STATUS_PAID;
        } else {
            $status = StudentBill::STATUS_PARTIAL;
        }

        $bill->update([
            'total_amount' => $total,
            'paid_amount' => $paid,
            'status' => $status,
            'generated_at' => $bill->generated_at ?? now(),
        ]);
    }

    /**
     * @return array<int,int>
     */
    public function monthsForSemester(Semester $semester): array
    {
        $label = strtolower(trim((string) ($semester->code ?: $semester->name)));
        if (str_contains($label, 'genap') || str_contains($label, 'even')) {
            return [1, 2, 3, 4, 5, 6];
        }

        return [7, 8, 9, 10, 11, 12];
    }

    public function monthName(int $month): string
    {
        return Carbon::createFromDate(2026, max(1, min(12, $month)), 1)
            ->locale(app()->getLocale())
            ->translatedFormat('F');
    }
}

