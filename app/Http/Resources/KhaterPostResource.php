<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\KhaterPostImageResource;

class KhaterPostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'title'     => $this->title,
            'excerpt'   => $this->excerpt,
            'date'      => optional($this->published_at)->format('Y-m-d'),
            'images'    => KhaterPostImageResource::collection($this->whenLoaded('images')),
        ];
    }
}

