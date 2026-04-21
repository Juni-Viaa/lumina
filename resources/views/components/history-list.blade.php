{{--
    Component: history-list
    Usage: @include('components.history-list', ['chatHistory' => $chatHistory])
--}}
<div class="flex flex-col gap-1">

    @forelse($chatHistory ?? [] as $chat)
        <a href="{{ route('chat.show', $chat->id) }}"
           x-show="!search || '{{ strtolower($chat->title ?? '') }}'.includes(search.toLowerCase())"
           class="history-item flex items-center gap-3 px-4 py-3.5 rounded-xl transition-all hover:bg-white/20 cursor-pointer group">
            <span class="text-[#1a3a52]/75 text-sm flex-1 truncate" style="font-family: 'Space Grotesk', sans-serif;">
                {{ $chat->title ?? 'Chat ' . $loop->iteration }}{{ str_repeat('.', 20) }}
            </span>
            <svg class="w-3.5 h-3.5 text-[#1a3a52]/20 opacity-0 group-hover:opacity-100 transition-opacity shrink-0"
                 fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
            </svg>
        </a>

    @empty
        {{-- Placeholder rows when no DB data yet --}}
        @foreach(['Chat 1', 'Chat 2', 'Chat 3'] as $placeholder)
        <div x-show="!search || '{{ strtolower($placeholder) }}'.includes(search.toLowerCase())"
             class="history-item flex items-center px-4 py-3.5 rounded-xl transition-all hover:bg-white/20 cursor-pointer">
            <span class="text-[#1a3a52]/75 text-sm" style="font-family: 'Space Grotesk', sans-serif;">
                {{ $placeholder }}............................
            </span>
        </div>
        @endforeach

        <div class="flex justify-end px-4 pt-1">
            <button class="text-xs text-[#1a3a52]/35 hover:text-[#1a3a52]/60 transition-colors"
                    style="font-family: 'Space Grotesk', sans-serif;">
                See more
            </button>
        </div>
    @endforelse

    {{-- No search results --}}
    <template x-if="search && document.querySelectorAll('.history-item[style*=\'none\']').length === document.querySelectorAll('.history-item').length">
        <div class="text-center py-12">
            <p class="text-sm text-[#1a3a52]/35" style="font-family: 'Space Grotesk', sans-serif;">
                Tidak ada hasil untuk "<span x-text="search"></span>"
            </p>
        </div>
    </template>

</div>
