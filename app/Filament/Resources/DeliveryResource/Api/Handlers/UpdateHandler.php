<?php
namespace App\Filament\Resources\DeliveryResource\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\DeliveryResource;
use App\Filament\Resources\DeliveryResource\Api\Requests\UpdateDeliveryRequest;

class UpdateHandler extends Handlers {
    public static string | null $uri = '/{id}';
    public static string | null $resource = DeliveryResource::class;

    public static function getMethod()
    {
        return Handlers::PUT;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }


    /**
     * Update Delivery
     *
     * @param UpdateDeliveryRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(UpdateDeliveryRequest $request)
    {
        $id = $request->route('id');

        $model = static::getModel()::find($id);

        if (!$model) return static::sendNotFoundResponse();

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Update Resource");
    }
}