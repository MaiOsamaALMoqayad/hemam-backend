<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatisticsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'beneficiaries_count' => $this->beneficiaries_count,
            'institutions_count' => $this->institutions_count,
            'trainings_count' => $this->trainings_count,
            'consultations_count' => $this->consultations_count,
        ];
    }
}
