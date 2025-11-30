<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->getTranslation('title'),
            'description' => $this->getTranslation('description'),
            'image' => $this->main_image ? asset('storage/' . $this->main_image) : null,
            'age_range' => $this->age_range,
            'start_date' => $this->start_date->format('Y-m-d'),
            'duration' => $this->duration,
            'capacity' => $this->capacity,
            'locations' => $this->locations->map(function ($location) {
                return $this->getTranslationFromJson($location->name);
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

    private function getTranslationFromJson($jsonData): string
    {
        $locale = app()->getLocale();

        if (is_string($jsonData)) {
            $jsonData = json_decode($jsonData, true);
        }

        return $jsonData[$locale] ?? $jsonData['ar'] ?? '';
    }
}
