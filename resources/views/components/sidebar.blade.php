{{--
    Component: sidebar
--}}
<aside class="flex flex-col h-full px-3 py-4 md:px-4 md:py-5 gap-2"
       x-data="sidebarApp()"
       x-init="loadHistory()">

    {{-- Logo --}}
    <div class="sidebar-logo-wrap flex items-center gap-2 px-1 mb-3">
        <div class="w-full h-9 rounded-lg glass-inner flex items-center leading-none overflow-hidden">
            <img src="{{ asset('images/icons/Logo.png') }}" class="w-12 h-8 opacity-70 shrink-0" alt="Logo">
            <span class="sidebar-brand-name text-[#1a3a52] font-semibold text-base tracking-tight truncate
                         hidden md:hidden lg:block"
                  style="font-family: 'Space Grotesk', sans-serif;">Lumina</span>
        </div>
    </div>

    {{-- Nav --}}
    <nav class="flex flex-col gap-1">

        {{-- New Chat --}}
        <a href="{{ route('dashboard.index') }}"
           class="sidebar-nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all
                  {{ request()->routeIs('dashboard.index') ? 'sidebar-nav-active' : '' }}">
            <img src="{{ asset('images/icons/NewChatIcon.png') }}" class="w-5 h-5 opacity-70 shrink-0" alt="">
            <span class="nav-label text-sm text-black/80 lg:block hidden">New Chat</span>
        </a>

        {{-- Upload — admin only --}}
        @auth
            @if(auth()->user()->role === 'admin')
            <a href="{{ route('uploads.index') }}"
               class="sidebar-nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all
                      {{ request()->routeIs('uploads.*') ? 'sidebar-nav-active' : '' }}">
                <img src="{{ asset('images/icons/UploadIcon.png') }}" class="w-5 h-5 opacity-70 shrink-0" alt="">
                <span class="nav-label text-sm text-black/80 lg:block hidden">Upload Dokumen</span>
            </a>
            @endif
        @endauth

        {{-- Riwayat Chat --}}
        <div class="history-section">
            <button @click="open = !open"
                    class="sidebar-nav-item w-full flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all">
                <img src="{{ asset('images/icons/HistoryIcon.png') }}" class="w-5 h-5 opacity-70 shrink-0" alt="">
                <span class="nav-label text-sm text-black/80 flex-1 text-left lg:block hidden">Riwayat Chat</span>
                <img src="{{ asset('images/icons/DropDownIcon.png') }}"
                     class="nav-label w-3.5 h-3.5 opacity-50 transition-transform duration-200 lg:block hidden"
                     :class="open ? 'rotate-180' : ''" alt="">
            </button>

            <div x-show="open"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="flex flex-col gap-0.5 pl-10 mt-0.5 lg:block hidden">

                <template x-if="loading">
                    <span class="text-xs text-[#1a3a52]/40 py-1 px-2 italic">Memuat...</span>
                </template>

                <template x-if="!loading && history.length > 0">
                    <div class="flex flex-col gap-0.5">
                        <template x-for="item in history" :key="item.query_id">
                            <a :href="`/history/${item.query_id}`"
                               class="text-xs text-[#1a3a52]/60 hover:text-[#1a3a52]/90 py-1.5 px-2
                                      rounded-lg hover:bg-white/20 transition-all truncate block"
                               :title="item.full_title">
                                <span x-text="item.title"></span>
                            </a>
                        </template>
                    </div>
                </template>

                <template x-if="!loading && history.length === 0">
                    <span class="text-xs text-[#1a3a52]/40 py-1 px-2 italic">Belum ada riwayat</span>
                </template>

                <a href="{{ route('history.index') }}" class="flex justify-end px-4 pt-1">
                    <button class="text-xs text-[#1a3a52]/35 hover:text-[#1a3a52]/60 transition-colors">
                        Lihat semua
                    </button>
                </a>
            </div>
        </div>
    </nav>

    <div class="flex-1"></div>

    {{-- Log Out --}}
    <form method="POST" action="{{ route('logout') }}" class="px-0.5">
        @csrf
        <div class="w-full rounded-lg glass-inner flex items-center leading-none">
            <button type="submit"
                    class="sidebar-nav-item w-full flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all">
                <img src="{{ asset('images/icons/LogOutIcon.png') }}" class="w-5 h-5 opacity-70 shrink-0" alt="">
                <span class="logout-label nav-label text-sm text-black/80 lg:block hidden">Log Out</span>
            </button>
        </div>
    </form>

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
                this.history = (data.items ?? []).slice(0, 5);
            } catch {
                this.history = [];
            } finally {
                this.loading = false;
            }
        },
    };
}

window.refreshSidebar = async function () {
    try {
        const res  = await fetch('{{ route("dashboard.history-json") }}');
        const data = await res.json();
        const items = (data.items ?? []).slice(0, 5);
        const sidebarEl = document.querySelector('[x-data="sidebarApp()"]');
        if (sidebarEl) {
            const alpineData = Alpine.$data(sidebarEl);
            if (alpineData) alpineData.history = items;
        }
    } catch { }
};
</script>