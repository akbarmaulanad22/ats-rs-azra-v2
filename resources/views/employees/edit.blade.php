<x-layouts.app title="Edit Karyawan - ATS RS Azra">

    <div class="mb-4">
        <a href="{{ route('karyawan.index') }}" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-primary transition-colors ease-out duration-150 mb-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Data Karyawan
        </a>
        <h1 class="text-xl font-semibold text-gray-900">Edit Karyawan</h1>
        <p class="text-xs text-gray-500 mt-0.5">{{ $employee->nama_karyawan }}</p>
    </div>

    <div class="bg-white/80 border border-gray-200 rounded-md">
        <form method="POST" action="{{ route('karyawan.update', $employee) }}">
            @csrf
            @method('PUT')

            {{-- Identitas --}}
            <div class="px-4 pt-4 pb-5">
                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-3">Identitas Karyawan</p>
                <div class="space-y-3">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="nip" class="block text-xs font-medium text-gray-700 mb-1">NIP <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                id="nip"
                                name="nip"
                                value="{{ old('nip', $employee->nip) }}"
                                class="w-full px-2.5 py-1.5 text-xs border rounded bg-white focus-ring @error('nip') border-red-400 @else border-gray-200 @enderror"
                            >
                            @error('nip')
                                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="nama_karyawan" class="block text-xs font-medium text-gray-700 mb-1">Nama Karyawan <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                id="nama_karyawan"
                                name="nama_karyawan"
                                value="{{ old('nama_karyawan', $employee->nama_karyawan) }}"
                                class="w-full px-2.5 py-1.5 text-xs border rounded bg-white focus-ring @error('nama_karyawan') border-red-400 @else border-gray-200 @enderror"
                            >
                            @error('nama_karyawan')
                                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <hr class="border-t border-gray-300/80">

            {{-- Informasi Pekerjaan --}}
            <div class="px-4 py-5">
                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-3">Informasi Pekerjaan</p>
                <div class="space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="unit" class="block text-xs font-medium text-gray-700 mb-1">Unit <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                id="unit"
                                name="unit"
                                value="{{ old('unit', $employee->unit) }}"
                                class="w-full px-2.5 py-1.5 text-xs border rounded bg-white focus-ring @error('unit') border-red-400 @else border-gray-200 @enderror"
                            >
                            @error('unit')
                                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="posisi_pekerjaan" class="block text-xs font-medium text-gray-700 mb-1">Posisi <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                id="posisi_pekerjaan"
                                name="posisi_pekerjaan"
                                value="{{ old('posisi_pekerjaan', $employee->posisi_pekerjaan) }}"
                                class="w-full px-2.5 py-1.5 text-xs border rounded bg-white focus-ring @error('posisi_pekerjaan') border-red-400 @else border-gray-200 @enderror"
                            >
                            @error('posisi_pekerjaan')
                                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="profesi" class="block text-xs font-medium text-gray-700 mb-1">Profesi <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                id="profesi"
                                name="profesi"
                                value="{{ old('profesi', $employee->profesi) }}"
                                class="w-full px-2.5 py-1.5 text-xs border rounded bg-white focus-ring @error('profesi') border-red-400 @else border-gray-200 @enderror"
                            >
                            @error('profesi')
                                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="jabatan" class="block text-xs font-medium text-gray-700 mb-1">Jabatan <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                id="jabatan"
                                name="jabatan"
                                value="{{ old('jabatan', $employee->jabatan) }}"
                                class="w-full px-2.5 py-1.5 text-xs border rounded bg-white focus-ring @error('jabatan') border-red-400 @else border-gray-200 @enderror"
                            >
                            @error('jabatan')
                                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                            @enderror
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
                    Perbarui Karyawan
                </button>
                <a href="{{ route('karyawan.index') }}" class="px-4 py-1.5 text-xs text-gray-500 border border-gray-300 rounded bg-white hover:bg-gray-50 transition-colors ease-out duration-150">
                    Batal
                </a>
            </div>
        </form>
    </div>

</x-layouts.app>
