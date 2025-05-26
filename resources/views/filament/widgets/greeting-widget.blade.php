<x-filament::widget>
    <div class="p-6 bg-white rounded-xl shadow dark:bg-gray-800">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
            {{ $this->getGreeting() }}, {{ $this->getUserName() }}! 👋
        </h2>
        
        @php
            $quote = $this->getQuote();
        @endphp
        <p class="text-gray-700 dark:text-gray-300 italic text-base leading-relaxed mb-2">
            "{{ $quote['text'] }}"
        </p>
        <p class="text-sm text-gray-500 dark:text-gray-400 text-right">
            — {{ $quote['author'] }}
        </p>
    </div>
</x-filament::widget>