<?php

namespace App\Filament\Resources\DeliveryResource\Api\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Delivery;

/**
 * @property Delivery $resource
 */
class DeliveryTransformer extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'delivery_number' => $this->delivery_number,
            'car_number' => $this->car->car_number ?? null,
            'recipient_name' => $this->recipient->name ?? null,
        ];
    }
}
