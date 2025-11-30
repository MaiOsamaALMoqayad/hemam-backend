<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->getTranslation('name'),
            'image' => $this->image ? asset('storage/' . $this->image) : null,
            'bio' => $this->getTranslation('bio'),
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
}
