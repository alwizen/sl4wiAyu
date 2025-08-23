<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKitchenPORequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 'source' => ['nullable', 'string', 'max:10'],
            'external_id' => ['required', 'string', 'max:50'],
            'requested_at' => ['required', 'date_format:Y-m-d'],
            'delivery_time' => ['required', 'date_format:H:i'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.warehouse_item_id' => ['nullable', 'integer', 'exists:warehouse_items,id'],
            'items.*.sku' => ['nullable', 'string', 'max:64'],
            'items.*.item_name' => ['nullable', 'string', 'max:191'],
            'items.*.qty' => ['required', 'numeric', 'gt:0'],
            'items.*.unit' => ['nullable', 'string', 'max:20'],
            'items.*.note' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $items = $this->input('items', []);
            foreach ($items as $i => $row) {
                if (empty($row['warehouse_item_id']) && empty($row['sku']) && empty($row['item_name'])) {
                    $v->errors()->add("items.$i.item", 'Minimal salah satu dari warehouse_item_id / sku / item_name harus diisi.');
                }
            }
        });
    }
}
