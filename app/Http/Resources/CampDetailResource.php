<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->getTranslation('title'),
            'main_image' => $this->main_image ? asset('storage/' . $this->main_image) : null,
            'info' => [
                'start_date' => $this->start_date->format('Y-m-d'),
                'locations' => $this->locations->map(function ($location) {
                    return $this->getTranslationFromJson($location->name);
                })->toArray(),
                'capacity' => $this->capacity,
                'duration' => $this->duration,
                'age_range' => $this->age_range,
            ],
            'about' => $this->getTranslation('about'),
            'learnings' => $this->learnings->map(function ($learning) {
                return $this->getTranslationFromJson($learning->title);
            })->toArray(),
            'activities' => $this->activities->map(function ($activity) {
                return [
                    'title' => $this->getTranslationFromJson($activity->title),
                    'description' => $this->getTranslationFromJson($activity->description),
                ];
            })->toArray(),
            'images' => $this->images->map(function ($image) {
                return asset('storage/' . $image->image);
            })->toArray(),
        ];
    }

    private function getTranslation(string $field): ?string
    {
        $locale = app()->getLocale();
        $data = $this->{$field};

        if (!$data) return null;

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
