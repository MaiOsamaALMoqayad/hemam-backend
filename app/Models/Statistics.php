<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Statistics extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'beneficiaries_count', 'institutions_count',
        'trainings_count', 'consultations_count'
    ];

    protected $casts = [
        'beneficiaries_count' => 'integer',
        'institutions_count' => 'integer',
        'trainings_count' => 'integer',
        'consultations_count' => 'integer',
        'updated_at' => 'datetime',
    ];

    public function incrementBeneficiaries($amount = 1)
    {
        $this->increment('beneficiaries_count', $amount);
        $this->touch('updated_at');
    }

    public function incrementInstitutions($amount = 1)
    {
        $this->increment('institutions_count', $amount);
        $this->touch('updated_at');
    }

    public function incrementTrainings($amount = 1)
    {
        $this->increment('trainings_count', $amount);
        $this->touch('updated_at');
    }

    public function incrementConsultations($amount = 1)
    {
        $this->increment('consultations_count', $amount);
        $this->touch('updated_at');
    }
}
