<?php

namespace App\Filament\Pages;

use App\Models\PurchaseOrder;
use App\Models\StockReceiving;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Actions;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Infolist;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Support\Colors\Color;
use Filament\Tables\Actions\Action as TableAction;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class PurchaseOrderTracking extends Page implements HasTable, HasForms, HasInfolists
{
    use InteractsWithTable;
    use InteractsWithForms;
    use InteractsWithInfolists;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static string $view = 'filament.pages.purchase-order-tracking';
    protected static ?string $title = 'Purchase Order Tracking';
    protected static ?string $navigationLabel = 'PO Tracking';
    protected static ?string $navigationGroup = 'Purchase Management';
    protected static ?int $navigationSort = 2;

    public ?int $selectedPurchaseOrderId = null;
    public ?PurchaseOrder $selectedPurchaseOrder = null;

    public function mount(): void
    {
        $this->form->fill();
        
        // Auto-select first PO if only one exists (optional)
        if (!$this->selectedPurchaseOrderId) {
            $firstPO = PurchaseOrder::first();
            if ($firstPO) {
                $this->selectedPurchaseOrderId = $firstPO->id;
                $this->selectedPurchaseOrder = PurchaseOrder::with([
                    'supplier',
                    'items.item',
                    'receivings.stockReceivingItems.warehouseItem'
                ])->find($firstPO->id);
                $this->form->fill(['selectedPurchaseOrderId' => $firstPO->id]);
            }
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('selectedPurchaseOrderId')
                    ->label('Select Purchase Order')
                    ->options(function () {
                        return PurchaseOrder::with('supplier')
                            ->get()
                            ->mapWithKeys(function ($po) {
                                return [
                                    $po->id => "{$po->order_number} - {$po->supplier->name} ({$po->order_date->format('d/m/Y')})"
                                ];
                            });
                    })
                    ->searchable()
                    ->live(onBlur: true)
                    ->reactive()
                    ->afterStateUpdated(function ($state) {
                        $this->loadPurchaseOrder($state);
                        $this->dispatch('po-changed'); // Dispatch event untuk memastikan update
                    })
                    ->placeholder('Choose a Purchase Order to track...')
                    ->columnSpanFull(),
                
                Actions::make([
                    Action::make('loadPO')
                        ->label('Load Purchase Order')
                        ->icon('heroicon-o-magnifying-glass')
                        ->color('primary')
                        ->action(function () {
                            $formData = $this->form->getState();
                            $selectedId = $formData['selectedPurchaseOrderId'] ?? $this->selectedPurchaseOrderId;
                            
                            if ($selectedId) {
                                $this->loadPurchaseOrder($selectedId);
                                
                                Notification::make()
                                    ->title('Purchase Order Loaded Successfully')
                                    ->body('PO #' . $this->selectedPurchaseOrder?->order_number . ' has been loaded.')
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Please select a Purchase Order first')
                                    ->body('Choose a Purchase Order from the dropdown above.')
                                    ->warning()
                                    ->send();
                            }
                        })
                ])
                ->columnSpanFull()
                ->alignCenter(),
            ])
            ->statePath('data')
            ->columns(1);
    }

    protected function loadPurchaseOrder(?int $poId): void
    {
        $this->selectedPurchaseOrderId = $poId;
        
        if ($poId) {
            $this->selectedPurchaseOrder = PurchaseOrder::with([
                'supplier',
                'items.item',
                'receivings.stockReceivingItems.warehouseItem'
            ])->find($poId);
        } else {
            $this->selectedPurchaseOrder = null;
        }
        
        // Force refresh semua komponen
        $this->resetTable();
        $this->dispatch('$refresh');
        
        // Update form state
        $this->form->fill(['selectedPurchaseOrderId' => $poId]);
    }

    public function purchaseOrderInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->selectedPurchaseOrder)
            ->schema([
                Section::make('Purchase Order Information')
                    ->schema([
                        TextEntry::make('order_number')
                            ->label('PO Number')
                            ->badge()
                            ->color(Color::Blue),
                        
                        TextEntry::make('supplier.name')
                            ->label('Supplier'),
                        
                        TextEntry::make('order_date')
                            ->label('Order Date')
                            ->date('d/m/Y'),
                        
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'approved' => 'success',
                                'cancelled' => 'danger',
                                default => 'gray',
                            }),
                        
                        TextEntry::make('total_amount')
                            ->label('Total Amount')
                            ->money('IDR'),
                        
                        TextEntry::make('delivery_status')
                            ->label('Delivery Status')
                            ->state(function ($record) {
                                if (!$record) return '-';
                                
                                $deliveryStatus = $this->calculateDeliveryStatus();
                                return $deliveryStatus['status'];
                            })
                            ->badge()
                            ->color(function ($record) {
                                if (!$record) return 'gray';
                                
                                $deliveryStatus = $this->calculateDeliveryStatus();
                                return match ($deliveryStatus['status']) {
                                    'Complete' => 'success',
                                    'Partial' => 'warning',
                                    'Over Delivery' => 'danger',
                                    'Not Started' => 'gray',
                                    default => 'gray',
                                };
                            }),
                    ])
                    ->columns(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                if (!$this->selectedPurchaseOrderId) {
                    return StockReceiving::query()->whereRaw('1 = 0'); // Return empty query
                }
                
                return StockReceiving::query()
                    ->where('purchase_order_id', $this->selectedPurchaseOrderId)
                    ->with(['stockReceivingItems.warehouseItem'])
                    ->orderBy('received_date', 'desc');
            })
            ->columns([
                TextColumn::make('received_date')
                    ->label('Delivery Date')
                    ->date('d/m/Y')
                    ->sortable(),
                
                TextColumn::make('items_summary')
                    ->label('Items Received')
                    ->state(function (StockReceiving $record) {
                        return $record->stockReceivingItems
                            ->map(function ($item) {
                                return $item->warehouseItem->name . ': ' . $item->received_quantity . ' ' . $item->warehouseItem->unit;
                            })
                            ->join(', ');
                    })
                    ->wrap(),
                
                TextColumn::make('total_items')
                    ->label('Total Items')
                    ->state(function (StockReceiving $record) {
                        return $record->stockReceivingItems->count() . ' items';
                    }),
                
                TextColumn::make('note')
                    ->label('Notes')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),
            ])
            ->actions([
                // TableAction::make('view_details')
                //     ->label('View Details')
                //     ->icon('heroicon-m-eye')
                //     ->modalHeading('Delivery Details')
                //     ->modalContent(function (StockReceiving $record) {
                //         return view('filament.pages.components.delivery-details', [
                //             'receiving' => $record,
                //             'purchaseOrder' => $this->selectedPurchaseOrder
                //         ]);
                //     })
                //     ->modalWidth('4xl'),
            ])
            ->emptyStateHeading('No Purchase Order Selected')
            ->emptyStateDescription('Please select a Purchase Order from the dropdown above to view delivery tracking.')
            ->emptyStateIcon('heroicon-o-truck');
    }

    private function calculateDeliveryStatus(): array
    {
        if (!$this->selectedPurchaseOrder) {
            return ['status' => 'Not Started', 'details' => []];
        }

        $orderItems = $this->selectedPurchaseOrder->items;
        $receivings = $this->selectedPurchaseOrder->receivings;

        if ($receivings->isEmpty()) {
            return ['status' => 'Not Started', 'details' => []];
        }

        $receivedTotals = [];
        foreach ($receivings as $receiving) {
            foreach ($receiving->stockReceivingItems as $item) {
                $warehouseItemId = $item->warehouse_item_id;
                if (!isset($receivedTotals[$warehouseItemId])) {
                    $receivedTotals[$warehouseItemId] = 0;
                }
                $receivedTotals[$warehouseItemId] += $item->received_quantity;
            }
        }

        $isComplete = true;
        $hasOverDelivery = false;
        $hasPartial = false;

        foreach ($orderItems as $orderItem) {
            $orderedQty = $orderItem->quantity;
            $receivedQty = $receivedTotals[$orderItem->item_id] ?? 0;

            if ($receivedQty > $orderedQty) {
                $hasOverDelivery = true;
            } elseif ($receivedQty < $orderedQty) {
                $isComplete = false;
                if ($receivedQty > 0) {
                    $hasPartial = true;
                }
            }
        }

        if ($hasOverDelivery) {
            return ['status' => 'Over Delivery', 'details' => $receivedTotals];
        } elseif ($isComplete) {
            return ['status' => 'Complete', 'details' => $receivedTotals];
        } elseif ($hasPartial) {
            return ['status' => 'Partial', 'details' => $receivedTotals];
        } else {
            return ['status' => 'Not Started', 'details' => $receivedTotals];
        }
    }

    public function getOrderItemsWithProgress(): array
    {
        if (!$this->selectedPurchaseOrder) {
            return [];
        }

        $orderItems = $this->selectedPurchaseOrder->items;
        $receivings = $this->selectedPurchaseOrder->receivings;

        // Calculate total received per item
        $receivedTotals = [];
        foreach ($receivings as $receiving) {
            foreach ($receiving->stockReceivingItems as $item) {
                $warehouseItemId = $item->warehouse_item_id;
                if (!isset($receivedTotals[$warehouseItemId])) {
                    $receivedTotals[$warehouseItemId] = 0;
                }
                $receivedTotals[$warehouseItemId] += $item->received_quantity;
            }
        }

        $result = [];
        foreach ($orderItems as $orderItem) {
            $receivedQty = $receivedTotals[$orderItem->item_id] ?? 0;
            $remainingQty = $orderItem->quantity - $receivedQty;
            $progressPercentage = $orderItem->quantity > 0 ? ($receivedQty / $orderItem->quantity) * 100 : 0;

            $result[] = [
                'item' => $orderItem->item,
                'ordered_quantity' => $orderItem->quantity,
                'received_quantity' => $receivedQty,
                'remaining_quantity' => max(0, $remainingQty),
                'progress_percentage' => min(100, $progressPercentage),
                'status' => $this->getItemStatus($receivedQty, $orderItem->quantity),
            ];
        }

        return $result;
    }

    private function getItemStatus($received, $ordered): string
    {
        if ($received == 0) return 'Not Started';
        if ($received >= $ordered) return 'Complete';
        return 'Partial';
    }

    // Method untuk refresh data secara manual jika diperlukan
    public function refreshData(): void
    {
        if ($this->selectedPurchaseOrderId) {
            $this->loadPurchaseOrder($this->selectedPurchaseOrderId);
            
            Notification::make()
                ->title('Data refreshed successfully')
                ->success()
                ->send();
        }
    }

    // Method untuk debugging
    public function debugFormState(): void
    {
        $formData = $this->form->getState();
        \Log::info('Form State Debug:', [
            'form_data' => $formData,
            'selected_property' => $this->selectedPurchaseOrderId,
            'has_po_object' => $this->selectedPurchaseOrder ? 'Yes' : 'No',
            'po_number' => $this->selectedPurchaseOrder?->order_number
        ]);
        
        Notification::make()
            ->title('Debug Info Logged')
            ->body('Check your logs for form state information')
            ->info()
            ->send();
    }

    // Method untuk handle perubahan PO via Livewire
    public function updatedSelectedPurchaseOrderId($value): void
    {
        \Log::info('PO ID Updated via Livewire:', ['value' => $value]);
        $this->loadPurchaseOrder($value);
    }

    // Method untuk memastikan data fresh ketika component di-render
    public function hydrate(): void
    {
        if ($this->selectedPurchaseOrderId && !$this->selectedPurchaseOrder) {
            \Log::info('Hydrating PO data:', ['id' => $this->selectedPurchaseOrderId]);
            $this->loadPurchaseOrder($this->selectedPurchaseOrderId);
        }
    }
}