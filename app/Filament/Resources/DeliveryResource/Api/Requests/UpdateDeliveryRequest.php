<?php

namespace App\Filament\Resources\DeliveryResource\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDeliveryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
			'delivery_number' => 'required',
			'short_code' => 'required',
			'delivery_date' => 'required|date',
			'recipient_id' => 'required',
			'qty' => 'required',
			'status' => 'required',
			'user_id' => 'required',
			'proof_delivery' => 'required',
			'prepared_at' => 'required',
			'shipped_at' => 'required',
			'received_at' => 'required',
			'returned_at' => 'required',
			'received_qty' => 'required',
			'returned_qty' => 'required',
			'car_id' => 'required'
		];
    }
}
