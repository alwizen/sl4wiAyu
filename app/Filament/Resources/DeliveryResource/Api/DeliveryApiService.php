<?php
namespace App\Filament\Resources\DeliveryResource\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Resources\DeliveryResource;
use Illuminate\Routing\Router;


class DeliveryApiService extends ApiService
{
    protected static string | null $resource = DeliveryResource::class;

    public static function handlers() : array
    {
        return [
            Handlers\CreateHandler::class,
            Handlers\UpdateHandler::class,
            Handlers\DeleteHandler::class,
            Handlers\PaginationHandler::class,
            Handlers\DetailHandler::class
        ];

    }
}
