<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->getTranslation('title'),
            'description' => $this->getTranslation('description'),
            'images' => $this->images->map(function ($image) {
                return asset('storage/' . $image->image);
            })->toArray(),
        ];
    }

    private function getTranslation(string $field): string
    {
        $locale = app()->getLocale();
        $data = $this->{$field};

        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        return $data[$locale] ?? $data['ar'] ?? '';
    }
}
