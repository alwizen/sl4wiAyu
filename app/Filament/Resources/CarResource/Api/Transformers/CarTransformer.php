<?php
namespace App\Filament\Resources\CarResource\Api\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Car;

/**
 * @property Car $resource
 */
class CarTransformer extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->resource->toArray();
    }
}
