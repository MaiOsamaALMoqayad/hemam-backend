<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ContactStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // الكل يقدر يستخدم الفورم
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^[0-9+\-\s()]+$/', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
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

            'phone.required' => 'رقم الجوال مطلوب',
            'phone.regex' => 'رقم الجوال غير صحيح',

            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'البريد الإلكتروني غير صحيح',

            'subject.required' => 'الموضوع مطلوب',
            'subject.max' => 'الموضوع يجب ألا يتجاوز 255 حرف',

            'message.required' => 'الرسالة مطلوبة',
            'message.min' => 'الرسالة يجب أن تكون 10 أحرف على الأقل',
            'message.max' => 'الرسالة يجب ألا تتجاوز 2000 حرف',
        ];
    }

    /**
     * Prepare data for validation (تنظيف البيانات).
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => strip_tags($this->name),
            'subject' => strip_tags($this->subject),
            'message' => strip_tags($this->message),
        ]);
    }

    /**
     * Handle a failed validation attempt.
     * 📌 هذا يضمن أن الرد دائمًا JSON في حال فشل الفورم.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'بيانات غير صالحة',
            'errors' => $validator->errors()
        ], 422));
    }
}
