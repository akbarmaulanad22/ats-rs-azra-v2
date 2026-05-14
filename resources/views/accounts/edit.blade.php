<x-layouts.app title="Edit Akun - ATS RS Azra">

    <div class="mb-4">
        <a href="{{ route('akun.index') }}" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-primary transition-colors ease-out duration-150 mb-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Akun Pengguna
        </a>
        <h1 class="text-xl font-semibold text-gray-900">Edit Akun</h1>
        @if ($user->employee)
            <p class="text-xs text-gray-500 mt-0.5">{{ $user->employee->nama_karyawan }} &middot; {{ $user->employee->nip }}</p>
        @endif
    </div>

    <div class="bg-white/80 border border-gray-200 rounded-md">
        <form method="POST" action="{{ route('akun.update', $user) }}">
            @csrf
            @method('PATCH')

            {{-- Informasi Akun --}}
            <div class="px-4 pt-4 pb-5">
                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-3">Informasi Akun</p>
                <div class="space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="username" class="block text-xs font-medium text-gray-700 mb-1">Username <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                id="username"
                                name="username"
                                value="{{ old('username', $user->username) }}"
                                class="w-full px-2.5 py-1.5 text-xs border rounded bg-white focus-ring font-mono @error('username') border-red-400 @else border-gray-200 @enderror"
                            >
                            @error('username')
                                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <x-autocomplete-select
                            name="role"
                            label="Role"
                            :options="collect($roles)->map(fn ($r) => ['id' => $r->value, 'label' => $r->label()])"
                            :value="old('role', $user->role->value)"
                            :required="true"
                            placeholder="Pilih role..."
                        />
                    </div>
                </div>
            </div>

            <hr class="border-t border-gray-300/80">

            {{-- Reset Kata Sandi --}}
            <div class="px-4 py-5">
                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Reset Kata Sandi</p>
                <p class="text-[11px] text-gray-400 mb-3">Kosongkan jika tidak ingin mengubah kata sandi. Jika diisi, karyawan wajib mengganti kata sandi saat login berikutnya.</p>
                <div class="space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="password" class="block text-xs font-medium text-gray-700 mb-1">Kata Sandi Baru</label>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Kosongkan jika tidak diubah"
                                class="w-full px-2.5 py-1.5 text-xs border rounded bg-white focus-ring @error('password') border-red-400 @else border-gray-200 @enderror"
                            >
                            @error('password')
                                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-xs font-medium text-gray-700 mb-1">Konfirmasi Kata Sandi Baru</label>
                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                placeholder="Ulangi kata sandi baru"
                                class="w-full px-2.5 py-1.5 text-xs border rounded bg-white focus-ring border-gray-200"
                            >
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center gap-2 px-4 py-3 border-t border-gray-200 bg-gray-200/90 rounded-b-md">
                <button
                    type="submit"
                    class="px-4 py-1.5 bg-primary text-white text-xs font-medium rounded hover:bg-primary-dark transition-colors ease-out duration-150 cursor-pointer"
                >
                    Simpan Perubahan
                </button>
                <a href="{{ route('akun.index') }}" class="px-4 py-1.5 text-xs text-gray-500 border border-gray-300 rounded bg-white hover:bg-gray-50 transition-colors ease-out duration-150">
                    Batal
                </a>
            </div>
        </form>
    </div>

</x-layouts.app>
