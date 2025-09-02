<x-filament-panels::page>
    <!-- Search Form -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            <div class="md:col-span-6">
                <label for="po_number" class="block text-sm font-medium text-gray-700 mb-2">
                    PO Number
                </label>
                <input 
                    type="text" 
                    id="po_number"
                    wire:model="po_number"
                    wire:keydown.enter="search"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                    placeholder="SPPG-SLAWI/2025-08-23/00001 (opsional)"
                >
            </div>
            <div class="md:col-span-3">
                <label class="flex items-center">
                    <input 
                        type="checkbox" 
                        wire:model="only_unverified"
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    >
                    <span class="ml-2 text-sm text-gray-700">Hanya yang belum diverifikasi</span>
                </label>
            </div>
            <div class="md:col-span-3">
                <button 
                    wire:click="search"
                    wire:loading.attr="disabled"
                    class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150"
                >
                    <svg wire:loading.remove class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <svg wire:loading class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span wire:loading.remove>Cari Item</span>
                    <span wire:loading>Mencari...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Results Table -->
    @if(count($this->rows) > 0)
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-medium text-gray-900">Items untuk Verifikasi</h3>
                <p class="text-sm text-gray-600">{{ count($this->rows) }} item ditemukan</p>
            </div>
            <button 
                wire:click="submitVerification"
                wire:confirm="Yakin ingin mengirim verifikasi?"
                wire:loading.attr="disabled"
                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150"
            >
                <svg wire:loading.remove class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                </svg>
                <svg wire:loading class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span wire:loading.remove>Kirim Verifikasi</span>
                <span wire:loading>Mengirim...</span>
            </button>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PO</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Dialokasikan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Qty Real</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Harga</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Verified Qty *</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catatan</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($this->rows as $index => $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $row['po_number'] ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $row['supplier'] ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            <div class="max-w-xs">
                                <div class="font-medium">{{ $row['item_name'] ?? '-' }}</div>
                                @if(isset($row['unit']))
                                <div class="text-xs text-gray-500">Unit: {{ $row['unit'] }}</div>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-center">{{ number_format($row['qty_allocated'] ?? 0, 3) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-center">{{ number_format($row['qty_real'] ?? 0, 3) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-center">{{ number_format($row['price'] ?? 0, 0) }}</td>
                        <td class="px-4 py-3">
                            <input 
                                type="number" 
                                step="0.001"
                                min="0"
                                wire:model.lazy="rows.{{ $index }}.verified_qty"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm text-center"
                                placeholder="0.000"
                            >
                        </td>
                        <td class="px-4 py-3">
                            <input 
                                type="text" 
                                wire:model.lazy="rows.{{ $index }}.note"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                placeholder="Catatan..."
                                maxlength="300"
                            >
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="px-6 py-3 bg-gray-50 text-xs text-gray-600">
            <span class="text-red-500">*</span> Verified Qty wajib diisi untuk item yang akan diverifikasi
        </div>
    </div>
    @elseif(!empty($this->po_number) || $this->only_unverified)
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
        <svg class="mx-auto h-12 w-12 text-yellow-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
        </svg>
        <p class="text-gray-600">Tidak ada item yang ditemukan. Coba ubah kriteria pencarian.</p>
    </div>
    @endif
</x-filament-panels::page>