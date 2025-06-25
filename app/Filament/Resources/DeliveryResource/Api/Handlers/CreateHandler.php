<?php
namespace App\Filament\Resources\DeliveryResource\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\DeliveryResource;
use App\Filament\Resources\DeliveryResource\Api\Requests\CreateDeliveryRequest;

class CreateHandler extends Handlers {
    public static string | null $uri = '/';
    public static string | null $resource = DeliveryResource::class;

    public static function getMethod()
    {
        return Handlers::POST;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }

    /**
     * Create Delivery
     *
     * @param CreateDeliveryRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(CreateDeliveryRequest $request)
    {
        $model = new (static::getModel());

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Create Resource");
    }
}