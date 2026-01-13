<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DonationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'country' => 'required|string|max:100',
            'email' => 'nullable|email',
            'amount' => 'required|numeric|min:1',
            'currency' => 'required|in:ILS,USD,EUR,TRY',
            'message' => 'nullable|string'
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'الاسم الكامل مطلوب',
            'phone.required' => 'رقم الجوال مطلوب',
            'country.required' => 'الدولة مطلوبة',
            'amount.required' => 'مبلغ التبرع مطلوب',
            'amount.min' => 'أقل مبلغ للتبرع هو 1',
            'currency.in' => 'العملة غير صالحة',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة'
        ];
    }
}
