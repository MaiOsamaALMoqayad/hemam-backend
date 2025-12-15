<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ConsultationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'whatsapp' => ['required', 'string', 'max:20', 'regex:/^\+?\d{6,20}$/'],
            'consultation_type' => ['required', 'in:educational,management,leadership,personal'],
            'consultation_details' => ['required', 'string', 'min:20', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'الاسم مطلوب',
            'name.string' => 'الاسم يجب أن يكون نصاً',
            'name.max' => 'الاسم يجب ألا يتجاوز 100 حرف',

            'whatsapp.required' => 'رقم الواتساب مطلوب',
            'whatsapp.string' => 'رقم الواتساب يجب أن يكون نصاً',
            'whatsapp.max' => 'رقم الواتساب يجب ألا يتجاوز 20 رقم',
            'whatsapp.regex' => 'رقم الواتساب غير صحيح (مثال: +970599123456)',

            'consultation_type.required' => 'نوع الاستشارة مطلوب',
            'consultation_type.in' => 'نوع الاستشارة يجب أن يكون أحد القيم: educational, management, leadership, personal',

            'consultation_details.required' => 'تفاصيل الاستشارة مطلوبة',
            'consultation_details.string' => 'تفاصيل الاستشارة يجب أن تكون نصاً',
            'consultation_details.min' => 'تفاصيل الاستشارة يجب أن تكون 20 حرف على الأقل',
            'consultation_details.max' => 'تفاصيل الاستشارة يجب ألا تتجاوز 2000 حرف',

            'notes.string' => 'الملاحظات يجب أن تكون نصاً',
            'notes.max' => 'الملاحظات يجب ألا تتجاوز 1000 حرف',
        ];
    }

    /**
     * Prepare data for validation.
     */
    protected function prepareForValidation(): void
    {
        // تنظيف البيانات
        $this->merge([
            'name' => $this->name ? strip_tags(trim($this->name)) : null,
            'whatsapp' => $this->whatsapp ? trim($this->whatsapp) : null,
            'consultation_details' => $this->consultation_details ? strip_tags(trim($this->consultation_details)) : null,
            'notes' => $this->notes ? strip_tags(trim($this->notes)) : null,
        ]);
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'خطأ في البيانات المدخلة',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
