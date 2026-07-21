@extends('layouts.app')
@section('title', 'Riwayat Pertanyaan')

@section('content')
<div class="glass-panel flex flex-col h-full overflow-hidden md:overflow-hidden" x-data="{ search: '' }">

    <div class="px-5 pt-5 pb-4 shrink-0">
        <h3 class="text-[#1a3a52] text-lg font-semibold leading-tight mb-4"
            style="font-family: 'Space Grotesk', sans-serif;">Riwayat Pertanyaan</h3>

        @include('components.search-bar', [
            'placeholder' => 'Cari pertanyaan...',
            'model'       => 'search',
        ])
    </div>

    <div class="flex-1 overflow-y-auto px-5 pb-24 md:pb-5">
        <div class="flex flex-col gap-1">

            @forelse($chatHistory as $chat)
                <a href="{{ route('dashboard.show', $chat->query_id) }}"
                   x-show="!search || '{{ strtolower($chat->display_title) }}'.includes(search.toLowerCase())"
                   class="history-item flex items-center gap-3 px-4 py-3.5 rounded-xl
                          transition-all hover:bg-white/20 cursor-pointer group">

                    <div class="min-w-0 flex-1">
                        <p class="text-[#1a3a52]/80 text-sm truncate"
                           style="font-family: 'Space Grotesk', sans-serif;">
                            {{-- FIX: use display_title accessor --}}
                            {{ $chat->display_title }}
                        </p>
                        <p class="text-[#1a3a52]/40 text-xs mt-0.5">
                            {{ $chat->created_at->diffForHumans() }}
                            @if($chat->status === 'answered')
                                · <span class="text-green-600">Terjawab</span>
                            @elseif($chat->status === 'failed')
                                · <span class="text-rose-500">Gagal</span>
                            @else
                                · <span class="text-yellow-600">Pending</span>
                            @endif
                        </p>
                    </div>

                    <svg class="w-3.5 h-3.5 text-[#1a3a52]/20 opacity-0
                                group-hover:opacity-100 transition-opacity shrink-0"
                         fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                    </svg>
                </a>
            @empty
                <div class="flex flex-col items-center justify-center py-20 text-center">
                    <p class="text-[#1a3a52]/50 text-sm">Belum ada riwayat pertanyaan.</p>
                    <a href="{{ route('dashboard.index') }}"
                       class="mt-3 text-[#1a6fa8] text-sm hover:underline">
                        Mulai chat sekarang
                    </a>
                </div>
            @endforelse

        </div>

        @if($chatHistory->hasPages())
            <div class="mt-6">{{ $chatHistory->links() }}</div>
        @endif
    </div>

</div>
@endsection