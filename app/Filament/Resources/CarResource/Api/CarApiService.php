<?php
namespace App\Filament\Resources\CarResource\Api;

use Rupadana\ApiService\ApiService;
use App\Filament\Resources\CarResource;
use Illuminate\Routing\Router;


class CarApiService extends ApiService
{
    protected static string | null $resource = CarResource::class;

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
