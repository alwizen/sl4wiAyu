<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Contracts\View\View;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;

class SearchPurchaseOrder extends Page implements HasForms
{
    use InteractsWithForms;

    public ?string $searchOrderNumber = null;

    protected static bool $shouldRegisterNavigation = false;


    public $purchaseOrders;

    protected static string $view = 'filament.pages.search-purchase-order';

    public function mount(): void
    {
        $this->form->fill();
        $this->purchaseOrders = collect();
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('searchOrderNumber')
                ->label('Nomor PO')
                ->required(),
        ];
    }

    public function search()
    {
        $data = $this->form->getState();

        $this->purchaseOrders = \App\Models\PurchaseOrder::with(['items.item', 'receivings.stockReceivingItems.warehouseItem'])
            ->where('order_number', 'like', '%' . $data['searchOrderNumber'] . '%')
            ->get();
    }
}
