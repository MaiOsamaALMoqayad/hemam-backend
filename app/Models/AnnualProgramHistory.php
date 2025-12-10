<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnualProgramHistory extends Model
{
    protected $fillable = ['annual_program_id', 'year', 'image', 'achievements'];

    protected $casts = [
        'achievements' => 'array',
    ];

public function program()
{
    return $this->belongsTo(AnnualProgram::class, 'annual_program_id');
}
}

