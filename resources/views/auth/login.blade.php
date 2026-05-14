<x-layouts.guest title="Masuk - ATS RS Azra">
    <div class="bg-white rounded-2xl shadow-sm px-8 py-9">

        {{-- Mobile-only branding (left panel hidden on small screens) --}}
        <div class="flex items-center gap-2.5 mb-7 lg:hidden">
            <div class="flex items-center justify-center w-7 h-7 rounded-lg bg-primary">
                <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M8 2h4v6h6v4h-6v6H8v-6H2V8h6V2z"/>
                </svg>
            </div>
            <span class="text-sm font-semibold text-gray-800">ATS RS Azra</span>
        </div>

        <h1 class="text-2xl font-bold text-gray-900 mb-0.5">Masuk</h1>
        <p class="text-sm text-gray-400 mb-7">Masukkan kredensial Anda untuk melanjutkan</p>

        @if (session('status'))
            <div class="mb-5 rounded-lg bg-green-50 border border-green-200 p-3.5">
                <p class="text-sm text-green-700">{{ session('status') }}</p>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="space-y-4 mb-5">
                <div>
                    <label for="username" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                        Nama Pengguna
                    </label>
                    <input
                        id="username"
                        name="username"
                        type="text"
                        value="{{ old('username') }}"
                        required
                        autofocus
                        placeholder="username"
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 transition-colors focus-ring px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-300"
                    >
                    @error('username')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                        Kata Sandi
                    </label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        placeholder="••••••••"
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 transition-colors focus-ring px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-300"
                    >
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center mb-6">
                <input
                    id="remember"
                    name="remember"
                    type="checkbox"
                    class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary focus:ring-offset-0"
                >
                <label for="remember" class="ml-2 text-sm text-gray-500">
                    Ingat saya
                </label>
            </div>

            <button
                type="submit"
                class="w-full bg-primary text-white rounded-lg py-3 px-4 hover:bg-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 font-semibold text-sm transition-colors ease-out duration-150 tracking-wide"
            >
                Masuk
            </button>
        </form>
    </div>
</x-layouts.guest>
