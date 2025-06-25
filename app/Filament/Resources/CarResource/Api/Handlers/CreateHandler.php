<?php
namespace App\Filament\Resources\CarResource\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\CarResource;
use App\Filament\Resources\CarResource\Api\Requests\CreateCarRequest;

class CreateHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = CarResource::class;

    public static function getMethod()
    {
        return Handlers::POST;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }

    /**
     * Create Car
     *
     * @param CreateCarRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(CreateCarRequest $request)
    {
        $model = new (static::getModel());

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Create Resource");
    }
}