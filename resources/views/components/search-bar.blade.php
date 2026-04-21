{{--
    Component: search-bar
    Usage: @include('components.search-bar', ['placeholder' => 'Cari Pertanyaan...', 'model' => 'search'])
--}}
<div class="glass-inner rounded-2xl flex items-center gap-3 px-4 py-3">
    <svg class="w-4 h-4 text-[#1a3a52]/30 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803a7.5 7.5 0 0010.607 10.607z"/>
    </svg>
    <input
        type="text"
        x-model="{{ $model ?? 'search' }}"
        placeholder="{{ $placeholder ?? 'Cari...' }}"
        class="flex-1 bg-transparent border-none outline-none text-sm text-[#1a3a52]/70 placeholder-[#1a3a52]/30"
        style="font-family: 'Space Grotesk', sans-serif;"
    >
</div>
