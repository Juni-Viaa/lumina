<x-guest-layout>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="flex rounded-3xl shadow-lg max-w-md mx-auto">
        <div class="glass-inner w-full max-w-md rounded-3xl overflow-hidden backdrop-blur-md border border-white/30">

            <!-- Header -->
            <div class="bg-blue-200/30 backdrop-blur-md text-center py-4 font-semibold text-lg text-black">
                Daftar
            </div>

            <!-- Body -->
            <div class="p-8">

                <!-- Logo -->
                <div class="w-24 h-24 mx-auto mb-6 rounded-lg">
                    <img src="{{ asset('images/icons/Logo.png') }}"
                        alt="Logo"
                        class="w-full h-full object-cover rounded-lg">
                </div>

                <form method="POST"
                    action="{{ route('register') }}"
                    class="space-y-6"
                    x-data="{ agree: {{ old('terms') ? 'true' : 'false' }} }">

                    @csrf

                    <!-- Email -->
                    <div>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="Email"
                            class="w-full px-0 py-1 border-0 border-b border-black/50 bg-transparent text-black placeholder:text-black/50 focus:outline-none focus:ring-0 focus:border-black">

                        <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-300" />
                    </div>

                    <!-- Username -->
                    <div>
                        <input
                            id="username"
                            type="text"
                            name="username"
                            value="{{ old('username') }}"
                            placeholder="Nama Pengguna"
                            class="w-full px-0 py-2 border-0 border-b border-black/50 bg-transparent text-black placeholder:text-black/50 focus:outline-none focus:ring-0 focus:border-black">

                        <x-input-error :messages="$errors->get('username')" class="mt-2 text-red-300" />
                    </div>

                    <!-- Password -->
                    <div>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            placeholder="Kata Sandi"
                            class="w-full px-0 py-1 border-0 border-b border-black/50 bg-transparent text-black placeholder:text-black/50 focus:outline-none focus:ring-0 focus:border-black">

                        <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-300" />
                    </div>

                    <!-- Konfirmasi Password -->
                    <div>
                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            placeholder="Konfirmasi Kata Sandi"
                            class="w-full px-0 py-1 border-0 border-b border-black/50 bg-transparent text-black placeholder:text-black/50 focus:outline-none focus:ring-0 focus:border-black">

                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-red-300" />
                    </div>

                    <!-- Terms & Conditions -->
                    <div>

                        <label class="flex items-start gap-3 cursor-pointer">

                            <input
                                type="checkbox"
                                name="terms"
                                value="1"
                                x-model="agree"
                                class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                {{ old('terms') ? 'checked' : '' }}>

                            <span class="text-sm text-black/70 leading-6">
                                Saya telah membaca dan menyetujui
                                <a href="{{ route('terms') }}"
                                    target="_blank"
                                    class="font-semibold text-blue-700 hover:underline">
                                    Syarat & Ketentuan
                                </a>.
                            </span>

                        </label>

                        <x-input-error :messages="$errors->get('terms')" class="mt-2 text-red-500" />

                    </div>

                    <!-- Register Button -->
                    <div class="text-center">

                        <button
                            type="submit"
                            :disabled="!agree"
                            :class="!agree
                                ? 'opacity-50 cursor-not-allowed'
                                : 'hover:bg-[#92C7DD]'"
                            class="glass-inner bg-[#C9DCE4] backdrop-blur-md text-black px-6 py-2 rounded-full text-sm transition duration-200">

                            Daftar

                        </button>

                    </div>

                    <!-- Login -->
                    <div class="flex justify-center text-xs text-black/70">

                        <a href="{{ route('login') }}"
                            class="hover:underline">

                            Sudah punya akun? Masuk

                        </a>

                    </div>

                </form>

            </div>

        </div>
    </div>

</x-guest-layout>