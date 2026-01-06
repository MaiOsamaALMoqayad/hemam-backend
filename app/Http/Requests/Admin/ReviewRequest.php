<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // لأن الأدمن محمي بالـ middleware
    }

    public function rules(): array
    {
        return [
            'person_name'  => 'required|string|max:255',
            'program_name' => 'required|string|max:255',
            'rating'       => 'required|integer|min:1|max:5',
            'comment'      => 'required|string',
            'is_published' => 'sometimes|boolean',
        ];
    }
}
