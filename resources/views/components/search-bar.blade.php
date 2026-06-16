{{--
    Component: search-bar
    Usage: @include('components.search-bar', ['placeholder' => 'Cari Pertanyaan...', 'model' => 'search'])
--}}
<div class="glass-inner rounded-2xl flex items-center gap-3 px-4 py-3">
    <svg class="w-4 h-4 text-[#1a3a52]/60 shrink-0" fill="#000000" viewBox="0 0 32 32" version="1.1" xmlns="http://www.w3.org/2000/svg" transform="rotate(90)"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> 
        <path d="M31.707 30.282l-9.717-9.776c1.811-2.169 2.902-4.96 2.902-8.007 0-6.904-5.596-12.5-12.5-12.5s-12.5 5.596-12.5 12.5 5.596 12.5 12.5 12.5c3.136 0 6.002-1.158 8.197-3.067l9.703 9.764c0.39 0.39 1.024 0.39 1.415 0s0.39-1.023 0-1.415zM12.393 23.016c-5.808 0-10.517-4.709-10.517-10.517s4.708-10.517 10.517-10.517c5.808 0 10.516 4.708 10.516 10.517s-4.709 10.517-10.517 10.517z"></path> </g>
    </svg>
    <input type="text" x-model="{{ $model ?? 'search' }}" placeholder="{{ $placeholder ?? 'Cari...' }}" class="flex-1 bg-transparent border-none outline-none text-sm text-[#1a3a52]/70 placeholder-[#1a3a52]/60" style="font-family: 'Space Grotesk', sans-serif;">
</div>
