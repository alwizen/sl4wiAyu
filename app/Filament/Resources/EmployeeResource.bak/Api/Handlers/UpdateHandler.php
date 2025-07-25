<?php
namespace App\Filament\Resources\EmployeeResource\Api\Handlers;

use Illuminate\Http\Request;
use Rupadana\ApiService\Http\Handlers;
use App\Filament\Resources\EmployeeResource;
use App\Filament\Resources\EmployeeResource\Api\Requests\UpdateEmployeeRequest;

class UpdateHandler extends Handlers {
    public static string | null $uri = '/{id}';
    public static string | null $resource = EmployeeResource::class;

    public static function getMethod()
    {
        return Handlers::PUT;
    }

    public static function getModel() {
        return static::$resource::getModel();
    }


    /**
     * Update Employee
     *
     * @param UpdateEmployeeRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler(UpdateEmployeeRequest $request)
    {
        $id = $request->route('id');

        $model = static::getModel()::find($id);

        if (!$model) return static::sendNotFoundResponse();

        $model->fill($request->all());

        $model->save();

        return static::sendSuccessResponse($model, "Successfully Update Resource");
    }
}