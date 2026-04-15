<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentBillPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'month_numbers' => ['required', 'array', 'min:1'],
            'month_numbers.*' => ['required', 'integer', 'between:1,12'],
            'payment_amount' => ['required', 'numeric', 'min:1'],
        ];
    }
}

