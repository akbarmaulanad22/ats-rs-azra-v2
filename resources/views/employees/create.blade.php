<x-layouts.app title="Tambah Karyawan - ATS RS Azra">

    <div class="mb-6">
        <a href="{{ route('karyawan.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-primary transition-colors ease-out duration-150 mb-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Data Karyawan
        </a>
        <h1 class="text-2xl font-bold text-gray-900 leading-tight">Tambah Karyawan</h1>
    </div>

    <div class="bg-white rounded-xl max-w-2xl">
        <form method="POST" action="{{ route('karyawan.store') }}">
            @csrf

            {{-- Section 1: Identitas --}}
            <div class="px-6 pt-6 pb-5">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Identitas Karyawan</p>
                <div class="space-y-4">
                    <div>
                        <label for="nip" class="block text-sm font-medium text-gray-700 mb-1.5">NIP <span class="text-red-500">*</span></label>
                        <input
                            type="text"
                            id="nip"
                            name="nip"
                            value="{{ old('nip') }}"
                            placeholder="Nomor Induk Pegawai"
                            class="w-full px-3 py-2 text-sm border rounded-lg focus-ring @error('nip') border-red-400 @else border-gray-200 @enderror"
                        >
                        @error('nip')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="nama_karyawan" class="block text-sm font-medium text-gray-700 mb-1.5">Nama Karyawan <span class="text-red-500">*</span></label>
                        <input
                            type="text"
                            id="nama_karyawan"
                            name="nama_karyawan"
                            value="{{ old('nama_karyawan') }}"
                            placeholder="Nama lengkap karyawan"
                            class="w-full px-3 py-2 text-sm border rounded-lg focus-ring @error('nama_karyawan') border-red-400 @else border-gray-200 @enderror"
                        >
                        @error('nama_karyawan')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-100 mx-6"></div>

            {{-- Section 2: Informasi Pekerjaan --}}
            <div class="px-6 py-5">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Informasi Pekerjaan</p>
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="unit" class="block text-sm font-medium text-gray-700 mb-1.5">Unit <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                id="unit"
                                name="unit"
                                value="{{ old('unit') }}"
                                placeholder="Contoh: ICU, HR, Finance"
                                class="w-full px-3 py-2 text-sm border rounded-lg focus-ring @error('unit') border-red-400 @else border-gray-200 @enderror"
                            >
                            @error('unit')
                                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="posisi_pekerjaan" class="block text-sm font-medium text-gray-700 mb-1.5">Posisi <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                id="posisi_pekerjaan"
                                name="posisi_pekerjaan"
                                value="{{ old('posisi_pekerjaan') }}"
                                placeholder="Posisi atau jabatan fungsional"
                                class="w-full px-3 py-2 text-sm border rounded-lg focus-ring @error('posisi_pekerjaan') border-red-400 @else border-gray-200 @enderror"
                            >
                            @error('posisi_pekerjaan')
                                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="profesi" class="block text-sm font-medium text-gray-700 mb-1.5">Profesi <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                id="profesi"
                                name="profesi"
                                value="{{ old('profesi') }}"
                                placeholder="Contoh: Perawat, Dokter"
                                class="w-full px-3 py-2 text-sm border rounded-lg focus-ring @error('profesi') border-red-400 @else border-gray-200 @enderror"
                            >
                            @error('profesi')
                                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="jabatan" class="block text-sm font-medium text-gray-700 mb-1.5">Jabatan <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                id="jabatan"
                                name="jabatan"
                                value="{{ old('jabatan') }}"
                                placeholder="Contoh: Staf, Koordinator"
                                class="w-full px-3 py-2 text-sm border rounded-lg focus-ring @error('jabatan') border-red-400 @else border-gray-200 @enderror"
                            >
                            @error('jabatan')
                                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer Actions --}}
            <div class="flex items-center gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-xl">
                <button
                    type="submit"
                    class="px-5 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors ease-out duration-150"
                >
                    Simpan Karyawan
                </button>
                <a href="{{ route('karyawan.index') }}" class="px-5 py-2 text-sm text-gray-500 border border-gray-200 rounded-lg hover:bg-white transition-colors ease-out duration-150">
                    Batal
                </a>
            </div>
        </form>
    </div>

</x-layouts.app>
