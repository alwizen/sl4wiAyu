<x-filament-panels::page>
    <!-- Search Form -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            <div class="md:col-span-6">
                <label for="po_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    PO Number
                </label>
                <input 
                    type="text" 
                    id="po_number"
                    wire:model="po_number"
                    wire:keydown.enter="search"
                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                    placeholder="SPPG-SLAWI/2025-08-23/00001 (opsional)"
                >
            </div>
            <div class="md:col-span-3">
                <label class="flex items-center">
                    <input 
                        type="checkbox" 
                        wire:model="only_unverified"
                        class="rounded border-gray-300 dark:border-gray-600 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    >
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Hanya yang belum diverifikasi</span>
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
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-750 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Items untuk Verifikasi</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ count($this->rows) }} item ditemukan</p>
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
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-750">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">PO</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Supplier</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Item</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider text-center">Dialokasikan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider text-center">Qty Real</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider text-center">Harga</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider text-center">Verified Qty *</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($this->rows as $index => $row)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $row['po_number'] ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $row['supplier'] ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                            <div class="max-w-xs">
                                <div class="font-medium">{{ $row['item_name'] ?? '-' }}</div>
                                @if(isset($row['unit']))
                                <div class="text-xs text-gray-500 dark:text-gray-400">Unit: {{ $row['unit'] }}</div>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 text-center">{{ number_format($row['qty_allocated'] ?? 0, 3) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 text-center">{{ number_format($row['qty_real'] ?? 0, 3) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 text-center">{{ number_format($row['price'] ?? 0, 0) }}</td>
                        <td class="px-4 py-3 text-center">
                            @if(isset($row['verified_qty']) && $row['verified_qty'] !== '')
                                <div class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-200">
                                    {{ number_format($row['verified_qty'], 3) }}
                                </div>
                            @else
                                <span class="text-gray-400 dark:text-gray-500 text-xs italic">Belum diisi</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <button 
                                wire:click="openModal({{ $index }})"
                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800 transition-colors duration-150"
                            >
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Input Timbangan
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="px-6 py-3 bg-gray-50 dark:bg-gray-750 text-xs text-gray-600 dark:text-gray-400">
            <span class="text-red-500">*</span> Verified Qty wajib diisi untuk item yang akan diverifikasi
        </div>
    </div>

    <!-- Modal Input -->
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" wire:ignore.self>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75" wire:click="closeModal"></div>
            
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">
                            Input Hasil Timbangan
                        </h3>
                        
                        @if(isset($this->rows[$selectedIndex]))
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 mb-4">
                            <div class="text-sm text-gray-600 dark:text-gray-400">Item:</div>
                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ $this->rows[$selectedIndex]['item_name'] ?? '-' }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Qty Real: {{ number_format($this->rows[$selectedIndex]['qty_real'] ?? 0, 3) }}
                            </div>
                        </div>
                        @endif
                        
                        <div class="space-y-4">
                            <div>
                                <label for="inputQty" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Hasil Timbangan <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="number" 
                                    id="inputQty"
                                    step="0.001"
                                    min="0"
                                    wire:model="inputQty"
                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                    placeholder="0.000"
                                    autofocus
                                >
                                @error('inputQty') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label for="inputNote" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Catatan
                                </label>
                                <textarea 
                                    id="inputNote"
                                    wire:model="inputNote"
                                    rows="3"
                                    maxlength="300"
                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                    placeholder="Tambahkan catatan jika diperlukan..."
                                ></textarea>
                                @error('inputNote') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button 
                        wire:click="saveQty"
                        type="button" 
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800 sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        Simpan
                    </button>
                    <button 
                        wire:click="closeModal"
                        type="button" 
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800 sm:mt-0 sm:w-auto sm:text-sm"
                    >
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @elseif(!empty($this->po_number) || $this->only_unverified)
    <div class="bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4 text-center">
        <svg class="mx-auto h-12 w-12 text-yellow-400 dark:text-yellow-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
        </svg>
        <p class="text-gray-600 dark:text-gray-300">Tidak ada item yang ditemukan. Coba ubah kriteria pencarian.</p>
    </div>
    @endif
</x-filament-panels::page>