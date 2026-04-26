<x-guest-layout>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="flex bg-slate-800/10 rounded-3xl shadow-lg max-w-md mx-auto">

        <div class="w-full max-w-md rounded-3xl overflow-hidden 
                    bg-white/20 backdrop-blur-md 
                    border border-black/30 
                    shadow-[0_0_40px_rgba(255,255,255,0.1)]">

            <!-- Header -->
            <div class="bg-blue/500/30 backdrop-blur-md text-center py-4 font-semibold text-lg text-black">
                Masuk
            </div>

            <!-- Body -->
            <div class="p-8">

                <!-- Logo -->
                <div class="w-24 h-24 bg-white/20 mx-auto mb-6 rounded-lg backdrop-blur-sm">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-full h-full object-cover rounded-lg">
                </div>

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

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

                    <!-- Button -->
                    <div class="text-center">
                        <button type="submit"
                            class="bg-black/20 backdrop-blur-md text-black px-6 py-2 rounded-full text-sm 
                                   hover:bg-white/30 transition">
                            Masuk
                        </button>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex justify-center text-sm text-black/70">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="remember" class="rounded bg-transparent border-black/50 focus:ring-0 focus:border-black">
                            Ingat Saya
                        </label>
                    </div>

                    <!-- Links -->
                    <div class="flex justify-between text-xs text-black/70">
                        <a href="{{ route('register') }}" class="hover:underline">
                            Belum punya akun?
                        </a>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="hover:underline">
                                Lupa Kata Sandi?
                            </a>
                        @endif
                    </div>

                </form>

            </div>
        </div>

    </div>

</x-guest-layout>