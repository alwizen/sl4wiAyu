
<x-filament::page>
    <form wire:submit.prevent="search" class="space-y-4">
        {{ $this->form }}
        <x-filament::button type="submit">
            Search
        </x-filament::button>
    </form>

    @if($purchaseOrders->count())
        <div class="mt-6 space-y-4">
            @foreach($purchaseOrders as $po)
                <div class="p-4 border rounded-lg">
                    <h2 class="font-bold text-lg">{{ $po->order_number }}</h2>
                    <p class="text-sm text-gray-600">Tanggal: {{ \Carbon\Carbon::parse($po->order_date)->format('d M Y') }}</p>

                    <h3 class="mt-3 font-semibold">Riwayat Penerimaan:</h3>
                    <ul class="list-disc list-inside text-sm">
                        @foreach($po->receivings as $receiving)
                            <li>
                                <strong>{{ \Carbon\Carbon::parse($receiving->received_date)->format('d M Y') }}</strong>
                                <ul class="list-disc list-inside ml-4">
                                    @foreach($receiving->stockReceivingItems as $item)
                                        <li>{{ $item->warehouseItem->name }} - {{ $item->received_quantity }}</li>
                                    @endforeach
                                </ul>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    @endif
</x-filament::page>

