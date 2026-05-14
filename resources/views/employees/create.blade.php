<x-layouts.app title="Tambah Karyawan - ATS RS Azra">

    <div class="mb-6">
        <a href="{{ route('karyawan.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-primary transition-colors mb-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali
        </a>
        <h1 class="text-2xl font-bold text-gray-900 leading-tight">Tambah Karyawan</h1>
    </div>

    <div class="bg-white rounded-xl p-6 max-w-2xl">
        <form method="POST" action="{{ route('karyawan.store') }}">
            @csrf

            <div class="space-y-5">
                <div>
                    <label for="nip" class="block text-sm font-medium text-gray-700 mb-1.5">NIP <span class="text-red-500">*</span></label>
                    <input
                        type="text"
                        id="nip"
                        name="nip"
                        value="{{ old('nip') }}"
                        class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary @error('nip') border-red-400 @else border-gray-200 @enderror"
                        placeholder="Nomor Induk Pegawai"
                    >
                    @error('nip')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="nama_karyawan" class="block text-sm font-medium text-gray-700 mb-1.5">Nama Karyawan <span class="text-red-500">*</span></label>
                    <input
                        type="text"
                        id="nama_karyawan"
                        name="nama_karyawan"
                        value="{{ old('nama_karyawan') }}"
                        class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary @error('nama_karyawan') border-red-400 @else border-gray-200 @enderror"
                        placeholder="Nama lengkap karyawan"
                    >
                    @error('nama_karyawan')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="unit" class="block text-sm font-medium text-gray-700 mb-1.5">Unit <span class="text-red-500">*</span></label>
                    <input
                        type="text"
                        id="unit"
                        name="unit"
                        value="{{ old('unit') }}"
                        class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary @error('unit') border-red-400 @else border-gray-200 @enderror"
                        placeholder="Contoh: ICU, HR, Finance"
                    >
                    @error('unit')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="posisi_pekerjaan" class="block text-sm font-medium text-gray-700 mb-1.5">Posisi Pekerjaan <span class="text-red-500">*</span></label>
                    <input
                        type="text"
                        id="posisi_pekerjaan"
                        name="posisi_pekerjaan"
                        value="{{ old('posisi_pekerjaan') }}"
                        class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary @error('posisi_pekerjaan') border-red-400 @else border-gray-200 @enderror"
                        placeholder="Posisi atau jabatan fungsional"
                    >
                    @error('posisi_pekerjaan')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="profesi" class="block text-sm font-medium text-gray-700 mb-1.5">Profesi <span class="text-red-500">*</span></label>
                    <input
                        type="text"
                        id="profesi"
                        name="profesi"
                        value="{{ old('profesi') }}"
                        class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary @error('profesi') border-red-400 @else border-gray-200 @enderror"
                        placeholder="Contoh: Perawat, Dokter, Apoteker"
                    >
                    @error('profesi')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="jabatan" class="block text-sm font-medium text-gray-700 mb-1.5">Jabatan <span class="text-red-500">*</span></label>
                    <input
                        type="text"
                        id="jabatan"
                        name="jabatan"
                        value="{{ old('jabatan') }}"
                        class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary @error('jabatan') border-red-400 @else border-gray-200 @enderror"
                        placeholder="Contoh: Staf, Koordinator, Kepala Unit"
                    >
                    @error('jabatan')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center gap-3 mt-7 pt-5 border-t border-gray-100">
                <button
                    type="submit"
                    class="px-5 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary/90 transition-colors"
                >
                    Simpan Karyawan
                </button>
                <a href="{{ route('karyawan.index') }}" class="px-5 py-2 text-sm text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>

</x-layouts.app>
