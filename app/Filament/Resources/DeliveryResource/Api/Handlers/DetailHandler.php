<?php

namespace App\Filament\Resources\DeliveryResource\Api\Handlers;

use App\Filament\Resources\SettingResource;
use App\Filament\Resources\DeliveryResource;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;
use App\Filament\Resources\DeliveryResource\Api\Transformers\DeliveryTransformer;

class DetailHandler extends Handlers
{
    public static string | null $uri = '/{id}';
    public static string | null $resource = DeliveryResource::class;


    /**
     * Show Delivery
     *
     * @param Request $request
     * @return DeliveryTransformer
     */
    public function handler(Request $request)
    {
        $id = $request->route('id');
        
        $query = static::getEloquentQuery();

        $query = QueryBuilder::for(
            $query->where(static::getKeyName(), $id)
        )
            ->first();

        if (!$query) return static::sendNotFoundResponse();

        return new DeliveryTransformer($query);
    }
}
