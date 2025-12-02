<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'whatsapp' => ['required', 'string', 'max:20','regex:/^\+\d{6,20}$/'],
            'consultation_type' => ['required', 'in:educational,administrative,leadership,personal'],
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
            'name.max' => 'الاسم يجب ألا يتجاوز 100 حرف',

            'whatsapp.required' => 'رقم الواتساب مطلوب',
            'whatsapp.max' => 'رقم الواتساب يجب ألا يتجاوز 20 رقم',


            'consultation_type.required' => 'نوع الاستشارة مطلوب',
            'consultation_type.in' => 'نوع الاستشارة غير صحيح',

            'consultation_details.required' => 'تفاصيل الاستشارة مطلوبة',
            'consultation_details.min' => 'تفاصيل الاستشارة يجب أن تكون 20 حرف على الأقل',
            'consultation_details.max' => 'تفاصيل الاستشارة يجب ألا تتجاوز 2000 حرف',

            'notes.max' => 'الملاحظات يجب ألا تتجاوز 1000 حرف',
        ];
    }

    /**
     * Prepare data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => strip_tags($this->name),
            'consultation_details' => strip_tags($this->consultation_details),
            'notes' => $this->notes ? strip_tags($this->notes) : null,
        ]);
    }
}
