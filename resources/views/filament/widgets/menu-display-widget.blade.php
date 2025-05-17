<x-filament::widget>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <h2 class="text-lg font-bold underline mb-2">Tabel Menu Hari Ini</h2>
            <ul class="list-disc list-inside">
                @foreach ($this->getData()['menus'] as $menu)
                    <li>{{ $menu }}</li>
                @endforeach
            </ul>
        </div>

        <div>
            <h2 class="text-lg font-bold underline mb-2">Target Group</h2>
            <ul class="list-disc list-inside">
                @foreach ($this->getData()['targetGroups'] as $group)
                    <li>{{ $group }}</li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="mt-6">
        <h2 class="text-lg font-bold mb-2">Pengiriman Hari Ini</h2>
        <table class="w-full text-sm border border-gray-200">
            <thead class="bg-gray-100 text-left">
                <tr>
                    <th class="p-2 border">Nama Sekolah Penerima</th>
                    <th class="p-2 border">Qty</th>
                    <th class="p-2 border">Status Pengiriman</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->getData()['deliveries'] as $delivery)
                    <tr>
                        <td class="p-2 border">{{ $delivery['recipient_name'] }}</td>
                        <td class="p-2 border text-center">{{ $delivery['qty'] }}</td>
                        <td class="p-2 border capitalize">{{ $delivery['status'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="p-2 text-center text-gray-500">Tidak ada pengiriman hari ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament::widget>
