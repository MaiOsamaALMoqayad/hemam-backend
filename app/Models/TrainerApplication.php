<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainerApplication extends Model
{
    protected $fillable = [
        'full_name', 'age', 'phone', 'country_code', 'email', 'residence',
        'gender', 'qualification', 'qualification_other', 'specialization',
        'experience_years', 'program_name', 'social_links', 'has_previous_courses',
        'courses_description', 'course_outcomes', 'about_me', 'training_fields',
        'training_field_other', 'status', 'admin_notes'
    ];

    protected $casts = [
        'social_links' => 'array',
        'training_fields' => 'array',
        'has_previous_courses' => 'boolean',
    ];

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function approve($notes = null)
    {
        $this->update(['status' => 'approved', 'admin_notes' => $notes]);
    }

    public function reject($notes = null)
    {
        $this->update(['status' => 'rejected', 'admin_notes' => $notes]);
    }
}
