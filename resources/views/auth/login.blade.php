<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Username -->
        <div>
            <x-input-label for="username" :value="__('Username')" />
            <x-text-input id="username" class="block mt-1 w-full" type="text" name="username" :value="old('username')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <div class="relative mt-1">
                <x-text-input id="password" class="block w-full pr-10"
                                type="password"
                                name="password"
                                required autocomplete="current-password" />
                <button type="button" onclick="togglePasswordVisibility()"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 transition">
                    <!-- Eye (visible) -->
                    <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <!-- Eye Off (hidden) -->
                    <svg id="eye-off-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                    </svg>
                </button>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>


        <!-- Remember Username -->
        <div class="block mt-4">
            <label for="remember_username" class="inline-flex items-center">
                <input id="remember_username" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <span class="ms-2 text-sm text-gray-600">{{ __('Ingat Username') }}</span>
            </label>
        </div>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end mt-4">
            <x-primary-button class="w-full justify-center sm:w-auto sm:ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>

    {{-- Toggle Password Visibility --}}
    <script>
    function togglePasswordVisibility() {
        var passwordInput = document.getElementById('password');
        var eyeIcon = document.getElementById('eye-icon');
        var eyeOffIcon = document.getElementById('eye-off-icon');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.add('hidden');
            eyeOffIcon.classList.remove('hidden');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('hidden');
            eyeOffIcon.classList.add('hidden');
        }
    }
    </script>

    {{-- Remember Username: simpan/muat username via localStorage --}}
    <script>
    (function() {
        var usernameInput = document.getElementById('username');
        var rememberCheck = document.getElementById('remember_username');


        // Muat username tersimpan
        var saved = localStorage.getItem('remembered_username');
        if (saved) {
            usernameInput.value = saved;
            rememberCheck.checked = true;
        }

        // Simpan username saat form disubmit
        document.querySelector('form').addEventListener('submit', function() {
            if (rememberCheck.checked) {
                localStorage.setItem('remembered_username', usernameInput.value.trim());
            } else {
                localStorage.removeItem('remembered_username');
            }
        });
    })();
    </script>

    {{-- =============================================================
         LOGIN ERROR POP-UP MODAL — Vanilla JS (biar pasti jalan)
         ============================================================= --}}
    @if($errors->has('username') || $errors->has('password') || $errors->has('email'))
    <div id="loginErrorModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        {{-- Overlay --}}
        <div id="loginErrorOverlay" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

        {{-- Modal Panel --}}
        <div class="relative z-10 w-full max-w-md bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">
            {{-- Header --}}
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-rose-50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-rose-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-rose-800">Login Gagal</h3>
                        <p class="text-sm text-rose-600">Terjadi kesalahan saat masuk</p>
                    </div>
                </div>
                <button onclick="closeLoginModal()" class="text-rose-400 hover:text-rose-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="px-6 py-5">
                <div class="flex items-start gap-3 p-4 bg-rose-50 rounded-lg border border-rose-200">
                    <svg class="w-5 h-5 shrink-0 text-rose-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-rose-800">
                            @if($errors->has('username') && $errors->has('password'))
                                Username dan Password tidak sesuai dengan data kami.
                            @elseif($errors->has('username'))
                                Username tidak ditemukan atau tidak sesuai.
                            @else
                                Password yang Anda masukkan salah.
                            @endif
                        </p>
                        <p class="text-xs text-rose-600 mt-1">Silakan periksa kembali dan coba lagi.</p>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end">
                <button onclick="closeLoginModal()" class="inline-flex items-center gap-2 bg-rose-600 hover:bg-rose-700 text-white font-semibold text-sm px-5 py-2.5 rounded-lg shadow-sm transition duration-150">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
        function closeLoginModal() {
            var modal = document.getElementById('loginErrorModal');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        document.getElementById('loginErrorOverlay').addEventListener('click', function() {
            closeLoginModal();
        });
    </script>
    @endif
</x-guest-layout>
