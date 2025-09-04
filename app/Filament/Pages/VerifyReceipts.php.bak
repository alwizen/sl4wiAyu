<?php

namespace App\Filament\Pages;

use App\Services\HubClient;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Arr;

class VerifyReceipts extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-scale';
    protected static string $view = 'filament.pages.verify-receipts';
    protected static ?string $navigationGroup = 'SPPG';
    protected static ?string $navigationLabel = 'Verifikasi Timbangan';

    // Form properties
    public string $po_number = '';
    public bool $only_unverified = true;
    public array $rows = [];

    // Modal properties
    public bool $showModal = false;
    public int $selectedIndex = 0;
    public string $inputQty = '';
    public string $inputNote = '';

    public function mount(): void
    {
        $this->po_number = '';
        $this->only_unverified = true;
        $this->rows = [];
        $this->showModal = false;
        $this->selectedIndex = 0;
        $this->inputQty = '';
        $this->inputNote = '';
    }

    public function search(): void
    {
        $this->validate([
            'po_number' => 'nullable|string|max:255',
        ]);

        $res = HubClient::fetchOpenReceiptItems(
            $this->po_number ?: null,
            $this->only_unverified
        );

        $this->rows = $res['data'] ?? [];

        Notification::make()
            ->title("Ditemukan " . count($this->rows) . " item")
            ->success()
            ->send();
    }

    public function openModal(int $index): void
    {
        $this->selectedIndex = $index;
        $this->inputQty = $this->rows[$index]['verified_qty'] ?? '';
        $this->inputNote = $this->rows[$index]['note'] ?? '';
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->inputQty = '';
        $this->inputNote = '';
    }

    public function saveQty(): void
    {
        $this->validate([
            'inputQty' => 'required|numeric|min:0',
            'inputNote' => 'nullable|string|max:300',
        ]);

        $this->rows[$this->selectedIndex]['verified_qty'] = $this->inputQty;
        $this->rows[$this->selectedIndex]['note'] = $this->inputNote;

        $this->closeModal();

        Notification::make()
            ->title('Data berhasil disimpan')
            ->success()
            ->send();
    }

    public function submitVerification(): void
    {
        $items = [];
        foreach ($this->rows as $row) {
            $vid = Arr::get($row, 'supplier_order_item_id');
            $vqty = Arr::get($row, 'verified_qty');
            if ($vid && $vqty !== null && $vqty !== '') {
                $items[] = [
                    'supplier_order_item_id' => $vid,
                    'verified_qty' => (string)$vqty,
                    'note' => Arr::get($row, 'note'),
                ];
            }
        }

        if (empty($items)) {
            Notification::make()
                ->title('Isi dulu Verified Qty.')
                ->danger()
                ->send();
            return;
        }

        HubClient::submitReceipt([
            'reference' => 'GRN-' . now()->format('Ymd-His'),
            'delivered_at' => now()->toIso8601String(),
            'items' => $items,
            'external' => ['weigher_name' => auth()->user()->name ?? 'SPPG'],
        ]);

        Notification::make()
            ->title('Verifikasi terkirim.')
            ->success()
            ->send();

        $this->rows = [];
        $this->po_number = '';
    }
}
