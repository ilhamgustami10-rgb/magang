<x-guest-layout>
    <!-- Session Status -->
    @if(session('status'))
        <div class="mb-5 text-sm font-medium text-emerald-300 bg-emerald-500/10 border border-emerald-400/20 rounded-xl px-4 py-3">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="label-glass block mb-2">Email</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-3.5 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-white/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                    </svg>
                </div>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                    class="form-input-glass w-full pl-10 pr-4 py-3 rounded-xl text-sm"
                    placeholder="email@airnav.co.id"
                />
            </div>
            @error('email')
                <p class="mt-2 text-xs font-semibold text-red-300">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="label-glass block mb-2">Password</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-3.5 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-white/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    class="form-input-glass w-full pl-10 pr-4 py-3 rounded-xl text-sm"
                    placeholder="••••••••"
                />
            </div>
            @error('password')
                <p class="mt-2 text-xs font-semibold text-red-300">{{ $message }}</p>
            @enderror
        </div>

        <!-- Remember Me + Forgot -->
        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer">
                <input
                    id="remember_me"
                    type="checkbox"
                    class="w-4 h-4 rounded border-white/30 bg-white/10 text-blue-500 focus:ring-blue-400 focus:ring-offset-0 transition"
                    name="remember"
                >
                <span class="text-sm text-white/55 font-medium">Ingat saya</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-semibold text-sky-300 hover:text-sky-200 transition-colors" href="{{ route('password.request') }}">
                    Lupa password?
                </a>
            @endif
        </div>

        <!-- Submit Button -->
        <button
            type="submit"
            class="btn-login w-full py-3.5 px-4 text-white font-bold text-sm rounded-xl tracking-wide"
        >
            Masuk ke Dashboard
        </button>
    </form>
</x-guest-layout>
