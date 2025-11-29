<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpertConsultation extends Model
{
    protected $fillable = [
        'name', 'whatsapp', 'country_code', 'consultation_type',
        'consultation_details', 'notes', 'status', 'admin_notes'
    ];

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function markInProgress($notes = null)
    {
        $this->update(['status' => 'in_progress', 'admin_notes' => $notes]);
    }

    public function markCompleted($notes = null)
    {
        $this->update(['status' => 'completed', 'admin_notes' => $notes]);
    }
}
