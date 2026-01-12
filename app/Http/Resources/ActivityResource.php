<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    public function toArray($request)
    {
        $title = is_array($this->title) ? $this->title : json_decode($this->title, true);
        $description = is_array($this->description) ? $this->description : json_decode($this->description, true);

        return [
            'id' => $this->id,
            'title' => [
                'ar' => $title['ar'] ?? '',
                'en' => $title['en'] ?? '',
            ],
            'description' => [
                'ar' => $description['ar'] ?? '',
                'en' => $description['en'] ?? '',
            ],
            'image' => $this->image ? asset('storage/' . $this->image) : null,
            'isOpen' => (bool)$this->is_open,
            'season' => $this->season, // الصيفي / الشتوي
            'applicationDeadline' => $this->application_deadline,
            'duration' => $this->duration,
            'capacity' => $this->capacity,
            'history' => $this->histories->map(function ($h) {
                return [
                    'id' => $h->id,
                    'year' => $h->year,
                    'achievements' => is_string($h->achievements)
                                      ? json_decode($h->achievements, true)
                                      : ($h->achievements ?? []),
                    'images' => $h->images->map(function ($img) {
                        return [
                            'id' => $img->id,
                            'image' => asset('storage/' . $img->image),
                        ];
                    }),
                ];
            }),
        ];
    }
}
