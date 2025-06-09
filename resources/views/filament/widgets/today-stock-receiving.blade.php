<x-filament::widget>
    <x-filament::card class="space-y-4">
        <h2 class="text-xl font-bold">Barang Masuk Hari Ini ({{ now()->format('d M Y') }})</h2><br>

        @forelse ($receivings as $receiving)
            <div class="border rounded-lg p-4 space-y-2 mb-4">
                <div>
                    <strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($receiving->received_date)->format('d M Y') }}
                </div>
                <div>
                    <strong>PO:</strong> {{ $receiving->purchaseOrder->order_number ?? '-' }}
                </div>
                <ul class="list-disc list-inside text-xl text-gray-500 space-y-1">
                    @foreach ($receiving->stockReceivingItems as $item)
                        <li>
                            {{ $item->warehouseItem->name ?? 'Item tidak ditemukan' }} -
                            <strong>{{ $item->received_quantity }}</strong>
                        </li>
                    @endforeach
                </ul>
            </div>
        @empty
            <p class="text-sm text-gray-500">Belum ada barang masuk hari ini.</p>
        @endforelse
    </x-filament::card>
</x-filament::widget>