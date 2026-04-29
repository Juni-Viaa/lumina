{{--
    Component: password-form
    Usage: @include('components.password-form')
--}}
<div class="flex flex-col h-full px-6 py-8" x-data="passwordForm()">

    {{-- Spacer top --}}
    <div class="flex-1"></div>

    {{-- Fields --}}
    <div class="flex flex-col gap-4">

        {{-- Password Lama --}}
        <div class="glass-inner rounded-2xl flex items-center px-5 py-4">
            <input
                type="password"
                x-model="form.old_password"
                placeholder="Password Lama"
                class="flex-1 bg-transparent border-none outline-none text-sm text-[#1a3a52]/70 placeholder-[#1a3a52]/50"
                style="font-family: 'Space Grotesk', sans-serif;"
                autocomplete="current-password"
            >
            <button type="button" @click="toggle('old')" class="text-[#1a3a52]/30 hover:text-[#1a3a52]/60 transition-colors ml-2">
                <svg x-show="!show.old" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <svg x-show="show.old" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                </svg>
            </button>
        </div>

        {{-- Password Baru --}}
        <div class="glass-inner rounded-2xl flex items-center px-5 py-4">
            <input
                :type="show.new ? 'text' : 'password'"
                x-model="form.new_password"
                placeholder="Password Baru"
                class="flex-1 bg-transparent border-none outline-none text-sm text-[#1a3a52]/70 placeholder-[#1a3a52]/50"
                style="font-family: 'Space Grotesk', sans-serif;"
                autocomplete="new-password"
            >
            <button type="button" @click="toggle('new')" class="text-[#1a3a52]/30 hover:text-[#1a3a52]/60 transition-colors ml-2">
                <svg x-show="!show.new" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <svg x-show="show.new" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                </svg>
            </button>
        </div>

        {{-- Konfirmasi Password Baru --}}
        <div class="glass-inner rounded-2xl flex items-center px-5 py-4"
             :class="form.confirm_password && form.new_password !== form.confirm_password ? 'ring-1 ring-red-400/40' : ''">
            <input
                :type="show.confirm ? 'text' : 'password'"
                x-model="form.confirm_password"
                placeholder="Konfirmasi Password Baru"
                class="flex-1 bg-transparent border-none outline-none text-sm text-[#1a3a52]/70 placeholder-[#1a3a52]/50"
                style="font-family: 'Space Grotesk', sans-serif;"
                autocomplete="new-password"
            >
            <button type="button" @click="toggle('confirm')" class="text-[#1a3a52]/30 hover:text-[#1a3a52]/60 transition-colors ml-2">
                <svg x-show="!show.confirm" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <svg x-show="show.confirm" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                </svg>
            </button>
        </div>

        {{-- Mismatch error --}}
        <p x-show="form.confirm_password && form.new_password !== form.confirm_password"
           class="text-xs text-red-400/70 -mt-2 px-1"
           style="font-family: 'Space Grotesk', sans-serif;">
            Password tidak cocok.
        </p>

    </div>

    {{-- Spacer middle --}}
    <div class="flex-1"></div>

    {{-- Action buttons --}}
    <div class="flex items-center justify-end gap-3 pt-4">

        {{-- Batal --}}
        <a href="{{ url()->previous() }}"
           class="glass-inner px-6 py-2.5 rounded-2xl text-sm text-[#1a3a52]/70 hover:bg-white/25 transition-all"
           style="font-family: 'Space Grotesk', sans-serif;">
            Batal
        </a>

        {{-- Simpan --}}
        <button
            @click="submit()"
            :disabled="saving || !canSubmit"
            class="glass-inner px-6 py-2.5 rounded-2xl text-sm font-medium text-[#1a3a52] hover:bg-white/30
                   disabled:opacity-40 disabled:cursor-not-allowed transition-all"
            style="font-family: 'Space Grotesk', sans-serif;">
            <span x-text="saving ? 'Menyimpan...' : 'Simpan'"></span>
        </button>

    </div>

    {{-- Success toast --}}
    <div x-show="success"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed bottom-6 right-6 glass-inner px-5 py-3 rounded-2xl text-sm text-emerald-600"
         style="font-family: 'Space Grotesk', sans-serif;">
        ✓ Password berhasil diubah.
    </div>

</div>

@push('scripts')
<script>
function passwordForm() {
    return {
        form: {
            old_password: '',
            new_password: '',
            confirm_password: '',
        },
        show: { old: false, new: false, confirm: false },
        saving: false,
        success: false,

        get canSubmit() {
            return this.form.old_password &&
                   this.form.new_password &&
                   this.form.new_password === this.form.confirm_password;
        },

        toggle(field) {
            this.show[field] = !this.show[field];
        },

        async submit() {
            if (!this.canSubmit) return;
            this.saving = true;
            try {
                const res = await fetch("{{ route('profile.password.update') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(this.form),
                });
                if (res.ok) {
                    this.success = true;
                    this.form = { old_password: '', new_password: '', confirm_password: '' };
                    setTimeout(() => this.success = false, 3000);
                }
            } finally {
                this.saving = false;
            }
        },
    }
}
</script>
@endpush
