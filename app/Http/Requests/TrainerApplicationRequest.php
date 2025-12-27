<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TrainerApplicationRequest extends FormRequest
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
            'full_name' => ['required', 'string', 'max:100'],
            'age' => ['required', 'integer', 'min:18', 'max:99'],
            'phone' => ['required', 'string', 'regex:/^\+\d{6,20}$/'],
            'email' => ['required', 'email', 'max:255', 'unique:trainer_applications,email'],
            'residence' => ['required', 'string', 'max:100'],
            'gender' => ['required', 'in:male,female'],
            'qualification' => ['required', 'in:high_school,bachelor,master,other'],
            'qualification_other' => ['required_if:qualification,other', 'nullable', 'string', 'max:100'],
            'specialization' => ['required', 'string', 'max:100'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:50'],
            'program_name' => ['required', 'string', 'max:100'],
            'social_links' => ['nullable', 'array'],
            'social_links.facebook' => ['nullable', 'string', 'max:255'],
            'social_links.instagram' => ['nullable', 'string', 'max:255'],
            'social_links.linkedin' => ['nullable', 'string', 'max:255'],
            'has_previous_courses' => ['required', 'boolean'],
            'courses_description' => ['required_if:has_previous_courses,true', 'nullable', 'string', 'max:1000'],
            'course_outcomes' => ['required', 'string', 'max:2000'],
            'about_me' => ['required', 'string', 'max:2000'],
            'training_fields' => ['required', 'array', 'min:1'],
            'training_fields.*' => ['string', 'in:leadership,management,education,guidance,odt_activities,psychological_support,sharia_sciences,other'],
            'training_field_other' => ['required_if:training_fields.*,other', 'nullable', 'string', 'max:100'],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'full_name.required' => 'الاسم الكامل مطلوب',
            'age.required' => 'العمر مطلوب',
            'age.min' => 'العمر يجب أن يكون 18 سنة على الأقل',
            'phone.required' => 'رقم الجوال مطلوب',
            'phone.regex' => 'رقم الجوال غير صحيح (يجب أن يبدأ بـ + وبدون مسافات)',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.unique' => 'البريد الإلكتروني مستخدم مسبقاً',
            'residence.required' => 'مكان الإقامة مطلوب',
            'gender.required' => 'الجنس مطلوب',
            'qualification.required' => 'المؤهل العلمي مطلوب',
            'qualification_other.required_if' => 'يرجى تحديد المؤهل العلمي',
            'specialization.required' => 'التخصص مطلوب',
            'experience_years.required' => 'عدد سنوات الخبرة مطلوب',
            'program_name.required' => 'اسم البرنامج مطلوب',
            'has_previous_courses.required' => 'يرجى تحديد إذا كنت قدمت دورات سابقاً',
            'courses_description.required_if' => 'يرجى وصف الدورات السابقة',
            'course_outcomes.required' => 'مخرجات الدورة مطلوبة',
            'about_me.required' => 'نبذة عنك مطلوبة',
            'training_fields.required' => 'المجال التدريبي مطلوب',
            'training_fields.min' => 'يرجى اختيار مجال تدريبي واحد على الأقل',
            'training_field_other.required_if' => 'يرجى تحديد المجال التدريبي',
        ];
    }

    /**
     * Prepare data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'full_name' => strip_tags($this->full_name),
            'specialization' => strip_tags($this->specialization),
            'program_name' => strip_tags($this->program_name),
        ]);
    }

    /**
     * اجبار الرد أن يكون JSON دائمًا عند فشل التحقق
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
