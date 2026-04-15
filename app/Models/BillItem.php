<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillItem extends Model
{
    public const STATUS_UNPAID = 'unpaid';
    public const STATUS_PARTIAL = 'partial';
    public const STATUS_PAID = 'paid';

    protected $fillable = [
        'student_bill_id',
        'month_number',
        'month_name',
        'amount',
        'paid_amount',
        'status',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(StudentBill::class, 'student_bill_id');
    }
}

