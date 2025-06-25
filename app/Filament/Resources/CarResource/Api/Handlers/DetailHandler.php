<?php

namespace App\Filament\Resources\CarResource\Api\Handlers;

use App\Filament\Resources\SettingResource;
use App\Filament\Resources\CarResource;
use Rupadana\ApiService\Http\Handlers;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;
use App\Filament\Resources\CarResource\Api\Transformers\CarTransformer;

class DetailHandler extends Handlers
{
    public static string | null $uri = '/{id}';
    public static string | null $resource = CarResource::class;


    /**
     * Show Car
     *
     * @param Request $request
     * @return CarTransformer
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

        return new CarTransformer($query);
    }
}
