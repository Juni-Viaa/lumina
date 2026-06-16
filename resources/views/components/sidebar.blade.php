{{--
    Component: sidebar
    Usage: @include('components.sidebar')
    Note: chatHistory is no longer passed as a prop — sidebar fetches
    its own history via the JSON endpoint so it stays consistent on
    every page and refreshes after new queries without a full reload.
--}}
<aside class="glass-panel sidebar flex flex-col h-full w-55 shrink-0 px-4 py-5 gap-2"
       x-data="sidebarApp()"
       x-init="loadHistory()">

    {{-- Logo --}}
    <div class="flex items-center gap-2.5 px-2 mb-3">
        <div class="w-full h-8 rounded-lg glass-inner flex items-center text-lg leading-none">
            <img src="{{ asset('images/icons/Logo.png') }}" class="w-12 h-8 opacity-70" alt="Logo">
            <span class="text-[#1a3a52] font-semibold text-base tracking-tight"
                  style="font-family: 'Space Grotesk', sans-serif;">Lumina</span>
        </div>
    </div>

    {{-- Nav --}}
    <nav class="flex flex-col gap-1">

        {{-- New Chat --}}
        <a href="{{ route('dashboard.index') }}"
           class="sidebar-nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all
                  {{ request()->routeIs('dashboard.index') ? 'sidebar-nav-active' : '' }}">
            <img src="{{ asset('images/icons/NewChatIcon.png') }}" class="w-5 h-5 opacity-70" alt="">
            <span class="text-sm text-black/80">New Chat</span>
        </a>

        {{-- Upload (Admin Only) --}}
            @if(auth()->check() && auth()->user()->role === 'admin')
                <a href="{{ route('uploads.index') }}"
                    class="sidebar-nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all
                        {{ request()->routeIs('uploads.*') ? 'sidebar-nav-active' : '' }}">
                <img src="{{ asset('images/icons/UploadIcon.png') }}" class="w-5 h-5 opacity-70" alt="">
                <span class="text-sm text-black/80">Upload Dokumen</span>
                </a>
            @endif

        {{-- Riwayat Chat --}}
        <div>
            <button @click="open = !open"
                    class="sidebar-nav-item w-full flex items-center gap-3 px-3 py-2.5
                           rounded-xl transition-all">
                <img src="{{ asset('images/icons/HistoryIcon.png') }}" class="w-5 h-5 opacity-70" alt="">
                <span class="text-sm text-black/80 flex-1 text-left">Riwayat Chat</span>
                <img src="{{ asset('images/icons/DropDownIcon.png') }}"
                     class="w-3.5 h-3.5 opacity-50 transition-transform duration-200"
                     :class="open ? 'rotate-180' : ''" alt="">
            </button>

            <div x-show="open"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="flex flex-col gap-0.5 pl-10 mt-0.5">

                {{-- Loading state --}}
                <template x-if="loading">
                    <span class="text-xs text-[#1a3a52]/40 py-1 px-2 italic">Memuat...</span>
                </template>

                {{-- History items — always exactly 5 --}}
                <template x-if="!loading && history.length > 0">
                    <div class="flex flex-col gap-0.5">
                        <template x-for="item in history" :key="item.query_id">
                            <a :href="`/history/${item.query_id}`"
                               class="text-sm text-[#1a3a52]/60 hover:text-[#1a3a52]/90 py-1.5 px-2
                                      rounded-lg hover:bg-white/20 transition-all truncate block"
                               :title="item.full_title">
                                <span x-text="item.title"></span>
                            </a>
                        </template>
                    </div>
                </template>

                {{-- Empty state --}}
                <template x-if="!loading && history.length === 0">
                    <span class="text-xs text-[#1a3a52]/40 py-1 px-2 italic">
                        Belum ada riwayat
                    </span>
                </template>

                {{-- See more — always visible --}}
                <a href="{{ route('history.index') }}" class="flex justify-end px-4 pt-1">
                    <button class="text-xs text-[#1a3a52]/35 hover:text-[#1a3a52]/60 transition-colors"
                            style="font-family: 'Space Grotesk', sans-serif;">
                        Lihat semua
                    </button>
                </a>
            </div>
        </div>
    </nav>
</aside>

<script>
function sidebarApp() {
    return {
        open:    true,
        loading: false,
        history: [],

        async loadHistory() {
            this.loading = true;
            try {
                const res  = await fetch('{{ route("dashboard.history-json") }}');
                const data = await res.json();
                this.history = (data.items ?? []).slice(0, 5); // always max 5
            } catch {
                this.history = [];
            } finally {
                this.loading = false;
            }
        },
    };
}

// Global function so dashboard chat can trigger a sidebar refresh
// after a new query without needing a page reload.
// Usage: window.refreshSidebar()
window.refreshSidebar = async function () {
    try {
        const res  = await fetch('{{ route("dashboard.history-json") }}');
        const data = await res.json();
        const items = (data.items ?? []).slice(0, 5);

        // Update Alpine component state directly
        const sidebarEl = document.querySelector('[x-data="sidebarApp()"]');
        if (sidebarEl && sidebarEl._x_dataStack) {
            const alpineData = Alpine.$data(sidebarEl);
            if (alpineData) alpineData.history = items;
        }
    } catch {
        // Silently fail — sidebar refresh is best-effort
    }
};
</script>