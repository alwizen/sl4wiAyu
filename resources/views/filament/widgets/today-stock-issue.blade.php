<x-filament::widget>
    <x-filament::card class="space-y-4">
        <h2 class="text-xl font-bold">Permintaan Bahan Masak Hari Ini ({{ now()->format('d M Y') }})</h2><br>

        @forelse ($issues as $issue)
            <div class="border rounded-lg p-4 space-y-2 mb-4">
                <div>
                    <strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($issue->issue_date)->format('d M Y') }}
                </div>
                <div>
                    <strong>Status:</strong> {{ $issue->status === 'Submitted' ? 'Selesai' : 'Diminta' }}
                </div>
                <ul class="list-disc list-inside text-xl text-gray-500 space-y-1">
                    @foreach ($issue->items as $item)
                        <li>
                            {{ $item->warehouseItem->name ?? 'Item tidak ditemukan' }} -
                            <strong>{{ $item->requested_quantity }}</strong>
                        </li>
                    @endforeach
                </ul>
            </div>
        @empty
            <p class="text-sm text-gray-500">Belum ada permintaan bahan masak hari ini.</p>
        @endforelse
    </x-filament::card>
</x-filament::widget>