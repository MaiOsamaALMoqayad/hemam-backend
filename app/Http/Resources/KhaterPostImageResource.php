<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KhaterPostImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'  => $this->id,
            'post_id' => $this->khater_post_id,
            'url' => asset('storage/' . $this->image_path),
        ];
    }
}

