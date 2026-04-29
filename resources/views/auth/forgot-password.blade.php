<x-guest-layout>

    <div class="flex items-center justify-center min-h-screen">

        <div class="w-full max-w-md rounded-3xl overflow-hidden 
                    bg-white/20 backdrop-blur-md 
                    border border-black/30 
                    shadow-[0_0_40px_rgba(255,255,255,0.1)]">

            <!-- Header -->
            <div class="bg-blue-500/30 backdrop-blur-md text-center py-4 font-semibold text-lg text-black">
                Lupa Password
            </div>

            <!-- Body -->
            <div class="p-8">

                <!-- Logo -->
                <div class="w-24 h-24 bg-white/20 mx-auto mb-6 rounded-lg backdrop-blur-sm flex items-center justify-center">
                    <img src="{{ asset('images/logo.png') }}" class="w-16 h-16 object-contain">
                </div>

                <!-- Deskripsi -->
                <p class="text-sm text-black/70 text-center mb-6">
                    Masukkan email kamu untuk mendapatkan link reset password
                </p>

                <!-- Session Status -->
                <x-auth-session-status class="mb-4 text-center text-green-600" :status="session('status')" />

                <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                    @csrf

                    <!-- Email -->
                    <div>
                        <input type="email" name="email" value="{{ old('email') }}"
                            placeholder="Email"
                            class="w-full px-0 py-2 border-0 border-b border-black/50 bg-transparent text-black placeholder:text-black/50 focus:outline-none focus:ring-0 focus:border-black">

                        <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-400" />
                    </div>

                    <!-- Button -->
                    <div class="text-center">
                        <button type="submit"
                            class="bg-black/20 backdrop-blur-md text-black px-6 py-2 rounded-full text-sm 
                                   hover:bg-white/30 transition">
                            Kirim Link Reset
                        </button>
                    </div>

                    <!-- Back to login -->
                    <div class="text-center text-xs text-black/70">
                        <a href="{{ route('login') }}" class="hover:underline">
                            Kembali ke Login
                        </a>
                    </div>

                </form>

            </div>
        </div>

    </div>

</x-guest-layout>