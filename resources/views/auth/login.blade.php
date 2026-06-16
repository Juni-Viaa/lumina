<x-guest-layout>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="flex rounded-3xl shadow-lg max-w-md mx-auto">
        <div class="glass-inner w-full max-w-md rounded-3xl overflow-hidden backdrop-blur-md border border-white/30">
            <!-- Header -->
            <div class="bg-blue-200/30 backdrop-blur-md text-center py-4 font-semibold text-lg text-black">
                Masuk
            </div>

            <!-- Body -->
            <div class="p-8">
                <!-- Logo -->
                <div class="w-24 h-24 bg-white/30 mx-auto mb-6 rounded-lg backdrop-blur-sm">
                    <img src="{{ asset('images/icons/Logo.png') }}" alt="Logo" class="w-full h-full object-cover rounded-lg">
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
                            class="glass-inner bg-[#C9DCE4] backdrop-blur-md text-black px-6 py-2 rounded-full text-sm  hover:bg-[#92C7DD] transition">
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
                   <div class="flex justify-center text-xs text-black/70">
                       <a href="{{ route('register') }}" class="hover:underline">
                           Belum punya akun?
                       </a>
                    </div>
                </form>

            </div>
        </div>

    </div>

</x-guest-layout>

@if ($errors->any())
<script>
    Swal.fire({
        icon: 'error',
        title: 'Login Gagal',
        text: '{{ $errors->first() }}',
        confirmButtonText: 'OK'
    });
</script>
@endif