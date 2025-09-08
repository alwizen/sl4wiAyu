<?php

namespace App\Filament\Resources\PayrollResource\Pages;

use App\Filament\Resources\PayrollResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPayroll extends EditRecord
{
    protected static string $resource = PayrollResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // jaga-jaga kalau user lupa klik "Hitung THP"
        $get = fn($key) => $data[$key] ?? null;
        $set = function ($key, $val) use (&$data) {
            $data[$key] = $val;
        };

        PayrollResource::updateTotalDays($get, $set);
        PayrollResource::hitungKehadiran($get, $set);
        PayrollResource::hitungTHP($get, $set);

        unset($data['show_thp']);
        unset($data['salary_per_day'], $data['allowance'], $data['absence_deduction']);

        return $data;
    }
}
