<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AnnualProgramSummaryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->getTranslated('title'),
            'description' => $this->getTranslated('description'),
            'image' => $this->image ? asset('storage/' . $this->image) : null,
            'is_open' => $this->is_open,
        ];
    }

    private function getTranslated(string $field): string
    {
        $data = $this->{$field};
        if (is_string($data)) {
            $data = json_decode($data, true);
        }
        return $data['ar'] ?? '';
    }
}
