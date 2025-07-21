<?php

namespace App\Filament\Resources\EmployeeResource\Api\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
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
			'nip' => 'required',
			'nik' => 'required',
			'rfid_uid' => 'required',
			'department_id' => 'required',
			'name' => 'required',
			'phone' => 'required',
			'address' => 'required',
			'start_join' => 'required|date',
			'work_type' => 'required'
		];
    }
}
