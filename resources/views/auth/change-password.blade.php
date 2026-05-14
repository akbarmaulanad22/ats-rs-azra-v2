<x-layouts.guest title="Ubah Kata Sandi - ATS RS Azra">
    <div class="bg-white rounded-xl shadow-sm p-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-1">Ubah Kata Sandi</h1>
        <p class="text-sm text-gray-500 mb-6">
            Anda harus mengubah kata sandi sebelum melanjutkan.
        </p>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <div class="mb-4">
                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">
                    Kata Sandi Saat Ini
                </label>
                <input
                    id="current_password"
                    name="current_password"
                    type="password"
                    required
                    autofocus
                    class="w-full rounded-lg border border-gray-300 transition-colors focus-ring px-3 py-2.5 text-sm text-gray-900"
                >
                @error('current_password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                    Kata Sandi Baru
                </label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    class="w-full rounded-lg border border-gray-300 transition-colors focus-ring px-3 py-2.5 text-sm text-gray-900"
                >
                @error('password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                    Konfirmasi Kata Sandi Baru
                </label>
                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    required
                    class="w-full rounded-lg border border-gray-300 transition-colors focus-ring px-3 py-2.5 text-sm text-gray-900"
                >
            </div>

            <button
                type="submit"
                class="w-full bg-primary text-white rounded-lg py-2.5 px-4 hover:bg-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 font-medium text-sm transition-colors ease-out duration-150"
            >
                Ubah Kata Sandi
            </button>
        </form>

        <div class="mt-4 text-center">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-gray-400 hover:text-gray-600 transition-colors ease-out duration-150">
                    Keluar
                </button>
            </form>
        </div>
    </div>
</x-layouts.guest>
