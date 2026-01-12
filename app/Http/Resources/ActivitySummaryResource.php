<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ActivitySummaryResource extends JsonResource
{
    public function toArray($request)
    {
        $title = is_array($this->title) ? $this->title : json_decode($this->title, true);

        return [
            'id' => $this->id,
            'title' => [
                'ar' => $title['ar'] ?? '',
                'en' => $title['en'] ?? '',
            ],
            'description' => [
                'ar' => $this->description['ar'] ?? '',
                'en' => $this->description['en'] ?? '',
            ],
            'image' => $this->image ? asset('storage/' . $this->image) : null,
            'is_open' => $this->is_open,
            'season' => $this->season,
        ];
    }
}
