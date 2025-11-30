<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnualProgramResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->getTranslation('title'),
            'description' => $this->getTranslation('description'),
            'image' => $this->image ? asset('storage/' . $this->image) : null,
        ];
    }

    /**
     * Get translated field
     */
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
