<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AnnualProgramResource extends JsonResource
{
  public function toArray($request)
{
    // تأكد من أن title و description هما arrays
    $title = is_array($this->title) ? $this->title : json_decode($this->title, true);
    $description = is_array($this->description) ? $this->description : json_decode($this->description, true);

    return [
        'id' => $this->id,
        'title' => $title['ar'] ?? '',
        'description' => $description['ar'] ?? '',
        'image' => $this->image ? asset('storage/' . $this->image) : null,
        'isOpen' => $this->is_open,
        'applicationDeadline' => $this->application_deadline,
        'duration' => $this->duration,
        'capacity' => $this->capacity,
        'history' => $this->histories->map(function($h) {
            return [
                'year' => $h->year,
                'image' => $h->image ? asset('storage/' . $h->image) : null,
                'achievements' => $h->achievements ?? [],
            ];
        }),
    ];
}
}
