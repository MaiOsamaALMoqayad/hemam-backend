<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AnnualProgramResource extends JsonResource
{
    public function toArray($request)
    {
        // تأكد من أن title و description هما arrays (مصفوفات)
        $title = is_array($this->title) ? $this->title : json_decode($this->title, true);
        $description = is_array($this->description) ? $this->description : json_decode($this->description, true);

        return [
            'id'          => $this->id,
            // التعديل هنا: نرسل المصفوفة كاملة بدل تحديد ['ar']
            'title'       => [
                'ar' => $title['ar'] ?? '',
                'en' => $title['en'] ?? '',
            ],
            'description' => [
                'ar' => $description['ar'] ?? '',
                'en' => $description['en'] ?? '',
            ],
            'image'               => $this->image ? asset('storage/' . $this->image) : null,
            'isOpen'              => (bool)$this->is_open,
            'applicationDeadline' => $this->application_deadline,
            'duration'            => $this->duration,
            'capacity'            => $this->capacity,
            'history'             => $this->histories->map(function($h) {
                return [
                    'id'           => $h->id,
                    'year'         => $h->year,
                    'image'        => $h->image ? asset('storage/' . $h->image) : null,
                    'achievements' => $h->achievements ?? [],
                ];
            }),
        ];
    }
}
