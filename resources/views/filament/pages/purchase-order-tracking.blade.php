{{-- resources/views/filament/pages/purchase-order-tracking.blade.php --}}
<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Form untuk memilih PO --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Select Purchase Order</h3>
                <div class="flex gap-2">
                    {{-- Debug button (remove in production) --}}
                    <button 
                        type="button"
                        wire:click="debugFormState"
                        class="inline-flex items-center px-2 py-1 border border-yellow-300 dark:border-yellow-600 shadow-sm text-xs font-medium rounded text-yellow-700 dark:text-yellow-200 bg-yellow-50 dark:bg-yellow-900 hover:bg-yellow-100 dark:hover:bg-yellow-800"
                    >
                        Debug
                    </button>
                    
                    @if($this->selectedPurchaseOrder)
                        <button 
                            type="button"
                            wire:click="refreshData"
                            class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                        >
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Refresh Data
                        </button>
                    @endif
                </div>
            </div>
            {{ $this->form }}
        </div>
        
        {{-- Info PO yang dipilih --}}
        @if($this->selectedPurchaseOrder)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow" wire:key="po-info-{{ $this->selectedPurchaseOrderId }}">
                <div class="p-6">
                    {{ $this->purchaseOrderInfolist }}
                </div>
            </div>
            
            {{-- Progress Summary --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6" wire:key="progress-{{ $this->selectedPurchaseOrderId }}">
                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Items Progress Summary</h3>
                @php
                    $itemsProgress = $this->getOrderItemsWithProgress();
                @endphp
                
                @if(count($itemsProgress) > 0)
                    <div class="space-y-4">
                        @foreach($itemsProgress as $index => $itemProgress)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-700" wire:key="item-{{ $this->selectedPurchaseOrderId }}-{{ $index }}">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ $itemProgress['item']->name }}</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Unit: {{ $itemProgress['item']->unit }}</p>
                                    </div>
                                    <span class="px-2 py-1 text-xs rounded-full
                                        @if($itemProgress['status'] === 'Complete') bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200
                                        @elseif($itemProgress['status'] === 'Partial') bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200
                                        @else bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200
                                        @endif">
                                        {{ $itemProgress['status'] }}
                                    </span>
                                </div>
                                
                                <div class="grid grid-cols-3 gap-4 text-sm mb-3">
                                    <div>
                                        <span class="text-gray-600 dark:text-gray-400">Ordered:</span>
                                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ number_format($itemProgress['ordered_quantity']) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600 dark:text-gray-400">Received:</span>
                                        <span class="font-medium text-green-600 dark:text-green-400">{{ number_format($itemProgress['received_quantity']) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600 dark:text-gray-400">Remaining:</span>
                                        <span class="font-medium text-orange-600 dark:text-orange-400">{{ number_format($itemProgress['remaining_quantity']) }}</span>
                                    </div>
                                </div>
                                
                                {{-- Progress Bar --}}
                                <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                                    <div class="bg-blue-600 dark:bg-blue-500 h-2 rounded-full transition-all duration-300" 
                                         style="width: {{ $itemProgress['progress_percentage'] }}%"></div>
                                </div>
                                <div class="text-right text-xs text-gray-600 dark:text-gray-400 mt-1">
                                    {{ number_format($itemProgress['progress_percentage'], 1) }}%
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                        <p>No items found for this purchase order.</p>
                    </div>
                @endif
            </div>
            
            {{-- Tabel riwayat pengiriman --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow" wire:key="table-{{ $this->selectedPurchaseOrderId }}">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Delivery History</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Track all deliveries for the selected purchase order</p>
                </div>
                <div class="p-6">
                    {{ $this->table }}
                </div>
            </div>
        @else
            {{-- Empty state ketika belum ada PO yang dipilih --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center">
                <div class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No Purchase Order Selected</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Please select a Purchase Order from the dropdown above to view tracking details.</p>
            </div>
        @endif
    </div>
</x-filament-panels::page>