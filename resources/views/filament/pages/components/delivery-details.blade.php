{{-- resources/views/filament/pages/purchase-order-tracking.blade.php --}}
<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Form untuk memilih PO --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Select Purchase Order</h3>
            {{ $this->form }}
        </div>
        
        {{-- Debug info (remove this in production) --}}
        {{-- 
        <div class="bg-yellow-50 border border-yellow-200 rounded p-4 text-sm">
            <strong>Debug:</strong> Selected ID: {{ $this->selectedPurchaseOrderId ?? 'null' }} | 
            Has PO: {{ $this->selectedPurchaseOrder ? 'Yes' : 'No' }}
        </div>
        --}}
        
        {{-- Info PO yang dipilih --}}
        @if($this->selectedPurchaseOrder)
            <div class="bg-white rounded-lg shadow">
                <div class="p-6">
                    {{ $this->purchaseOrderInfolist }}
                </div>
            </div>
            
            {{-- Progress Summary --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Items Progress Summary</h3>
                @php
                    $itemsProgress = $this->getOrderItemsWithProgress();
                @endphp
                
                @if(count($itemsProgress) > 0)
                    <div class="space-y-4">
                        @foreach($itemsProgress as $itemProgress)
                            <div class="border rounded-lg p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h4 class="font-medium">{{ $itemProgress['item']->name }}</h4>
                                        <p class="text-sm text-gray-600">Unit: {{ $itemProgress['item']->unit }}</p>
                                    </div>
                                    <span class="px-2 py-1 text-xs rounded-full
                                        @if($itemProgress['status'] === 'Complete') bg-green-100 text-green-800
                                        @elseif($itemProgress['status'] === 'Partial') bg-yellow-100 text-yellow-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ $itemProgress['status'] }}
                                    </span>
                                </div>
                                
                                <div class="grid grid-cols-3 gap-4 text-sm mb-3">
                                    <div>
                                        <span class="text-gray-600">Ordered:</span>
                                        <span class="font-medium">{{ number_format($itemProgress['ordered_quantity']) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Received:</span>
                                        <span class="font-medium text-green-600">{{ number_format($itemProgress['received_quantity']) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Remaining:</span>
                                        <span class="font-medium text-orange-600">{{ number_format($itemProgress['remaining_quantity']) }}</span>
                                    </div>
                                </div>
                                
                                {{-- Progress Bar --}}
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                                         style="width: {{ $itemProgress['progress_percentage'] }}%"></div>
                                </div>
                                <div class="text-right text-xs text-gray-600 mt-1">
                                    {{ number_format($itemProgress['progress_percentage'], 1) }}%
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <p>No items found for this purchase order.</p>
                    </div>
                @endif
            </div>
            
            {{-- Tabel riwayat pengiriman --}}
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold">Delivery History</h3>
                    <p class="text-sm text-gray-600 mt-1">Track all deliveries for the selected purchase order</p>
                </div>
                <div class="p-6">
                    {{ $this->table }}
                </div>
            </div>
        @else
            {{-- Empty state ketika belum ada PO yang dipilih --}}
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <div class="mx-auto h-12 w-12 text-gray-400">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No Purchase Order Selected</h3>
                <p class="mt-1 text-sm text-gray-500">Please select a Purchase Order from the dropdown above to view tracking details.</p>
            </div>
        @endif
    </div>
</x-filament-panels::page>