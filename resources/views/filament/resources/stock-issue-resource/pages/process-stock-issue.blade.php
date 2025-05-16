<x-filament::page>
    <div class="space-y-6">
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="px-4 py-5 sm:px-6 bg-primary-500">
                <h3 class="text-lg leading-6 font-medium text-white">
                    Proses Penyiapan Bahan
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-white">
                    ID: {{ $record->id }} | Tanggal: {{ $record->issue_date->format('d/m/Y') }} | Status: {{ ucfirst($record->status) }}
                </p>
            </div>
            <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">
                            Referensi PO
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $record->purchaseOrder->id ?? 'N/A' }}
                        </dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">
                            Catatan
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $record->notes ?? '-' }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="p-4 bg-yellow-50 rounded-xl border border-yellow-200">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">
                        Petunjuk Penyiapan Bahan
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>
                            1. Periksa stok yang tersedia untuk setiap item<br>
                            2. Isi jumlah yang disiapkan (tidak boleh melebihi stok tersedia)<br>
                            3. Klik "Simpan" untuk menyimpan data sementara<br>
                            4. Klik "Selesaikan & Kurangi Stok" untuk menyelesaikan proses dan mengurangi stok secara otomatis
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <form wire:submit.prevent="save" class="space-y-6">
            {{ $this->form }}

            <div class="flex justify-end space-x-2">
                @foreach ($this->getActions() as $action)
                    {{ $action }}
                @endforeach
            </div>
        </form>
    </div>
</x-filament::page>