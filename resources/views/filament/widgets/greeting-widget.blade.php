<x-filament::widget>
    <div class="p-6 bg-white rounded-xl shadow dark:bg-gray-800">
        <h2 class="text-2xl font-bold">
            {{ $this->getGreeting() }}, {{ $this->getUserName() }}! 👋
        </h2><br>
        @php
            $quote = $this->getQuote();
        @endphp
        <p class="mt-4 text-gray-700 dark:text-gray-200 italic text-lg">
            “{{ $quote['text'] }}”
        </p>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 text-right">
            — {{ $quote['author'] }}
        </p>
    </div>
</x-filament::widget>
