<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoryImage extends Model
{
    protected $fillable = ['history_id', 'image'];

    public function history()
    {
        return $this->belongsTo(AnnualProgramHistory::class);
    }
}

