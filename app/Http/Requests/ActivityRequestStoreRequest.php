<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
class ActivityRequestStoreRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'age' => 'required|integer|min:10|max:80',
            'phone' => 'required|string|max:20',
            'gender' => 'required|in:male,female',
            'activity_id' => 'required|exists:activities,id',
        ];
    }

    public function authorize()
    {
        return true;
    }
}
