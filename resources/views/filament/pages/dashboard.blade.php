<x-filament::page>
    <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="mb-4 text-xl font-bold text-success-600 dark:text-success-400">
            {{ $greeting }}
        </div>

        <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
            <div class="text-gray-300 dark:text-gray-300 italic">
                "{{ $quote['text'] }}"
            </div>
            <div class="mt-2 text-sm text-gray-600 dark:text-gray-400 text-right">
                — {{ $quote['author'] }}
            </div>
        </div>
    </div>

    <!-- Konten dashboard Anda -->
    <div class="mt-8">
        <!-- Tambahkan konten dashboard Anda di sini -->
    </div>
</x-filament::page>
