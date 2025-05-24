<x-filament::page>
    <x-filament::card class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
            <div class="bg-green-100 p-4 rounded-lg shadow">
                <div class="text-sm text-green-700">Total Pemasukan</div>
                <div class="text-2xl font-bold text-green-800">
                    Rp {{ number_format($this->getSummary()['income'], 0, ',', '.') }}
                </div>
            </div>
            <div class="bg-red-100 p-4 rounded-lg shadow">
                <div class="text-sm text-red-700">Total Pengeluaran</div>
                <div class="text-2xl font-bold text-red-800">
                    Rp {{ number_format($this->getSummary()['expense'], 0, ',', '.') }}
                </div>
            </div>
            <div class="bg-blue-100 p-4 rounded-lg shadow">
                <div class="text-sm text-blue-700">Saldo Akhir</div>
                <div class="text-2xl font-bold text-blue-800">
                    Rp {{ number_format($this->getSummary()['balance'], 0, ',', '.') }}
                </div>
            </div>
        </div>
    </x-filament::card>

    {{ $this->table }}
</x-filament::page>
