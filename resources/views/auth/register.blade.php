<x-guest-layout>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="flex bg-slate-800/10 rounded-3xl shadow-lg max-w-md mx-auto">

        <div class="w-full max-w-md rounded-3xl overflow-hidden 
                    bg-white/20 backdrop-blur-md 
                    border border-black/30 
                    shadow-[0_0_40px_rgba(255,255,255,0.1)]">

            <!-- Header -->
            <div class="bg-blue-200/30 backdrop-blur-md text-center py-4 font-semibold text-lg text-black">
                Daftar
            </div>

            <!-- Body -->
            <div class="p-8">

                <!-- Logo -->
                <div class="w-24 h-24 bg-white/20 mx-auto mb-6 rounded-lg backdrop-blur-sm">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-full h-full object-cover rounded-lg">
                </div>

                <form method="POST" action="{{ route('register') }}" class="space-y-6">
                    @csrf

                    <!-- Email -->
                    <div>
                        <input id="email" type="email" name="email" value="{{ old('email') }}"
                            placeholder="Email"
                            class="w-full px-0 py-2 border-0 border-b border-black/50 bg-transparent text-black placeholder:text-black/50 focus:outline-none focus:ring-0 focus:border-black">
                        
                        <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-300" />
                    </div>

                     <!-- Username -->
                    <div>
                        <input id="username" type="text" name="username" value="{{ old('username') }}"
                            placeholder="Nama Pengguna"
                            class="w-full px-0 py-2 border-0 border-b border-black/50 bg-transparent text-black placeholder:text-black/50 focus:outline-none focus:ring-0 focus:border-black">

                        <x-input-error :messages="$errors->get('username')" class="mt-2 text-red-300" />
                    </div>

                    <!-- Password -->
                    <div>
                        <input id="password" type="password" name="password"
                            placeholder="Kata Sandi"
                            class="w-full px-0 py-2 border-0 border-b border-black/50 bg-transparent text-black placeholder:text-black/50 focus:outline-none focus:ring-0 focus:border-black">

                        <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-300" />
                    </div>

                    <!-- Konfirmasi Password -->
                    <div>
                        <input type="password" name="password_confirmation"
                            placeholder="Konfirmasi Kata Sandi"
                            class="w-full px-0 py-2 border-0 border-b border-black/50 bg-transparent text-black placeholder:text-black/50 focus:outline-none focus:ring-0 focus:border-black">

                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-red-300" />
                    </div>

                    <!-- Button -->
                    <div class="text-center">
                        <button type="submit"
                            class="bg-black/20 backdrop-blur-md text-black px-6 py-2 rounded-full text-sm 
                                   hover:bg-white/30 transition">
                            Daftar
                        </button>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex justify-center text-sm text-black/70">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="terms" class="rounded bg-transparent border-black/50 focus:ring-0 focus:border-black">
                            Syarat dan Ketentuan
                        </label>
                    </div>

                    <!-- Already have an account? -->
                    <div class="flex justify-center text-xs text-black/70">
                        <a href="{{ route('login') }}" class="hover:underline">
                            Sudah punya akun? Masuk
                    </div>

                </form>

            </div>
        </div>

    </div>

</x-guest-layout>