<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnualProgramHistory extends Model
{
    protected $fillable = ['annual_program_id', 'year', 'achievements'];

    protected $casts = [
        'achievements' => 'array',
    ];

public function program()
{
    return $this->belongsTo(AnnualProgram::class, 'annual_program_id');
}
public function images()
{
    return $this->hasMany(HistoryImage::class, 'history_id');
}

}

