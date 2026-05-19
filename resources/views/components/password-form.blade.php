{{--
    Component: password-form
--}}
<div class="flex flex-col h-full px-6 py-8" x-data="passwordForm()">

    {{-- Spacer --}}
    <div class="flex-1"></div>

    {{-- Form Fields --}}
    <div class="flex flex-col gap-4">

        {{-- Password Lama --}}
        <div class="glass-inner rounded-2xl flex items-center px-5 py-4">
            <input
                :type="show.old ? 'text' : 'password'"
                x-model="form.old_password"
                placeholder="Password Lama"
                autocomplete="current-password"
                class="flex-1 bg-transparent border-none outline-none text-sm
                       text-[#1a3a52]/70 placeholder-[#1a3a52]/50"
            >

            <button
                type="button"
                @click="toggle('old')"
                class="ml-2 text-[#1a3a52]/30 hover:text-[#1a3a52]/60 transition-colors"
            >
                <template x-if="!show.old">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                         stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M2.036 12.322a1.012 1.012 0 010-.639C3.423
                              7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007
                              9.963 7.178.07.207.07.431 0 .639C20.577
                              16.49 16.64 19.5 12 19.5c-4.638
                              0-8.573-3.007-9.963-7.178z"/>
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </template>

                <template x-if="show.old">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                         stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 3l18 18"/>
                    </svg>
                </template>
            </button>
        </div>

        {{-- Password Baru --}}
        <div class="glass-inner rounded-2xl flex items-center px-5 py-4">
            <input
                :type="show.new ? 'text' : 'password'"
                x-model="form.new_password"
                placeholder="Password Baru"
                autocomplete="new-password"
                class="flex-1 bg-transparent border-none outline-none text-sm
                       text-[#1a3a52]/70 placeholder-[#1a3a52]/50"
            >

            <button
                type="button"
                @click="toggle('new')"
                class="ml-2 text-[#1a3a52]/30 hover:text-[#1a3a52]/60 transition-colors"
            >
                <template x-if="!show.new">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                         stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M2.036 12.322a1.012 1.012 0 010-.639C3.423
                              7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007
                              9.963 7.178.07.207.07.431 0 .639C20.577
                              16.49 16.64 19.5 12 19.5c-4.638
                              0-8.573-3.007-9.963-7.178z"/>
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </template>

                <template x-if="show.new">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                         stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 3l18 18"/>
                    </svg>
                </template>
            </button>
        </div>

        {{-- Konfirmasi Password --}}
        <div
            class="glass-inner rounded-2xl flex items-center px-5 py-4"
            :class="mismatch ? 'ring-1 ring-red-400/40' : ''"
        >
            <input
                :type="show.confirm ? 'text' : 'password'"
                x-model="form.confirm_password"
                placeholder="Konfirmasi Password Baru"
                autocomplete="new-password"
                class="flex-1 bg-transparent border-none outline-none text-sm
                       text-[#1a3a52]/70 placeholder-[#1a3a52]/50"
            >

            <button
                type="button"
                @click="toggle('confirm')"
                class="ml-2 text-[#1a3a52]/30 hover:text-[#1a3a52]/60 transition-colors"
            >
                <template x-if="!show.confirm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                         stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M2.036 12.322a1.012 1.012 0 010-.639C3.423
                              7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007
                              9.963 7.178.07.207.07.431 0 .639C20.577
                              16.49 16.64 19.5 12 19.5c-4.638
                              0-8.573-3.007-9.963-7.178z"/>
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </template>

                <template x-if="show.confirm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                         stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 3l18 18"/>
                    </svg>
                </template>
            </button>
        </div>

        {{-- Error --}}
        <p
            x-show="mismatch"
            class="text-xs text-red-400/70 px-1 -mt-2"
        >
            Password tidak cocok.
        </p>

    </div>

    {{-- Spacer --}}
    <div class="flex-1"></div>

    {{-- Actions --}}
    <div class="flex items-center justify-end gap-3 pt-4">

        <a
            href="{{ url()->previous() }}"
            class="glass-inner px-6 py-2.5 rounded-2xl text-sm
                   text-[#1a3a52]/70 hover:bg-white/25 transition-all"
        >
            Batal
        </a>

        <button
            @click="submit()"
            :disabled="saving || !canSubmit"
            class="glass-inner px-6 py-2.5 rounded-2xl text-sm font-medium
                   text-[#1a3a52] hover:bg-white/30
                   disabled:opacity-40 disabled:cursor-not-allowed transition-all"
        >
            <span x-text="saving ? 'Menyimpan...' : 'Simpan'"></span>
        </button>

    </div>

    {{-- Success Toast --}}
    <div
        x-show="success"
        x-transition
        class="fixed bottom-6 right-6 glass-inner px-5 py-3 rounded-2xl
               text-sm text-emerald-600"
    >
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

        show: {
            old: false,
            new: false,
            confirm: false,
        },

        saving: false,
        success: false,

        get mismatch() {
            return this.form.confirm_password &&
                   this.form.new_password !== this.form.confirm_password;
        },

        get canSubmit() {
            return this.form.old_password &&
                   this.form.new_password &&
                   !this.mismatch;
        },

        toggle(field) {
            this.show[field] = !this.show[field];
        },

        async submit() {

            if (!this.canSubmit) return;

            this.saving = true;

            try {

                const response = await fetch(
                    "{{ route('profile.password.update') }}",
                    {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document
                                .querySelector('meta[name=\"csrf-token\"]')
                                .content,
                        },
                        body: JSON.stringify(this.form),
                    }
                );

                const data = await response.json();

                if (!response.ok) {
                    alert(data.message || 'Gagal mengubah password');
                    return;
                }

                this.success = true;

                this.form = {
                    old_password: '',
                    new_password: '',
                    confirm_password: '',
                };

                setTimeout(() => {
                    this.success = false;
                }, 3000);

            } catch (error) {

                alert('Terjadi kesalahan sistem.');

            } finally {

                this.saving = false;

            }
        },
    }
}
</script>
@endpush