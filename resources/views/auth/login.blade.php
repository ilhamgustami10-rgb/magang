<x-guest-layout>
    <!-- Session Status -->
    @if(session('status'))
        <div class="mb-5 text-sm font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg px-4 py-3">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4 mt-2">
        @csrf

        <!-- Email Address -->
        <div>
            <div class="relative">
                <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none">
                    <!-- Envelope Icon -->
                    <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                    class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-blue-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:ring-4 focus:ring-blue-500/20 focus:border-blue-600 focus:bg-white transition-all shadow-sm"
                    placeholder="Alamat Email"
                />
            </div>
            @error('email')
                <p class="mt-1.5 text-xs font-semibold text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div>
            <div class="relative">
                <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none">
                    <!-- Lock Icon -->
                    <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    class="w-full pl-12 pr-12 py-3.5 bg-slate-50 border border-blue-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:ring-4 focus:ring-blue-500/20 focus:border-blue-600 focus:bg-white transition-all shadow-sm"
                    placeholder="Kata Sandi"
                />
                <button type="button" id="togglePassword" class="absolute inset-y-0 right-4 flex items-center text-slate-400 hover:text-blue-600 transition-colors">
                    <!-- Eye Icon -->
                    <svg id="eyeOpen" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <!-- Eye Slash Icon (hidden by default) -->
                    <svg id="eyeClosed" class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                </button>
            </div>
            @error('password')
                <p class="mt-1.5 text-xs font-semibold text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Remember Me + Forgot -->
        <div class="flex items-center justify-between pt-1">
            <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer">
                <input
                    id="remember_me"
                    type="checkbox"
                    class="w-4 h-4 rounded border-slate-300 text-blue-700 focus:ring-blue-600 transition"
                    name="remember"
                >
                <span class="text-sm text-slate-600 font-medium cursor-pointer">Ingat saya</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-semibold text-blue-600 hover:text-blue-800 transition-colors" href="{{ route('password.request') }}">
                    Lupa password?
                </a>
            @endif
        </div>

        <!-- Submit Button -->
        <button
            type="submit"
            class="w-full py-3.5 px-4 mt-2 bg-gradient-to-r from-blue-900 to-blue-600 hover:from-blue-800 hover:to-blue-500 text-white font-bold text-sm rounded-xl tracking-wide shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200"
        >
            Masuk ke Dashboard
        </button>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const eyeOpen = document.getElementById('eyeOpen');
            const eyeClosed = document.getElementById('eyeClosed');

            if(togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    
                    if(type === 'text') {
                        eyeOpen.classList.add('hidden');
                        eyeClosed.classList.remove('hidden');
                    } else {
                        eyeOpen.classList.remove('hidden');
                        eyeClosed.classList.add('hidden');
                    }
                });
            }
        });
    </script>
</x-guest-layout>
