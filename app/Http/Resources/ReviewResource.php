<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'person_name' => $this->person_name,
            'is_published' => (bool) $this->is_published,
            'activity' => [
                'id'    => $this->activity->id,
                'title' => $this->activity->title ?? null
            ],
            'rating'      => $this->rating,
            'comment'     => $this->comment,
            'created_at'  => $this->created_at->format('Y-m-d'),
        ];
    }
}
