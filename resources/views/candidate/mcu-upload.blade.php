<x-layouts.public title="Upload Dokumen MCU - RS Azra">

    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
        <div class="bg-primary px-6 py-6">
            <h1 class="text-xl font-bold text-white mb-1">Upload Dokumen MCU</h1>
            <p class="text-white/70 text-sm">{{ $application->vacancy->judul_posisi }} &mdash; {{ $application->vacancy->unit->nama }}</p>
        </div>

        <div class="px-6 py-6 space-y-6">

            <div>
                <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Informasi Pelamar</h2>
                <dl class="space-y-2">
                    <div class="flex gap-3">
                        <dt class="text-xs text-gray-400 w-28 shrink-0">Nama</dt>
                        <dd class="text-sm text-gray-800 font-medium">{{ $application->candidate->nama_lengkap }}</dd>
                    </div>
                    <div class="flex gap-3">
                        <dt class="text-xs text-gray-400 w-28 shrink-0">Posisi</dt>
                        <dd class="text-sm text-gray-800">{{ $application->vacancy->judul_posisi }}</dd>
                    </div>
                </dl>
            </div>

            @if (session('success'))
                <div class="px-4 py-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="px-4 py-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <div>
                <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Dokumen MCU Saat Ini</h2>
                @if ($application->mcuResult?->dokumen_path)
                    <div class="flex items-center gap-2 px-3 py-2 bg-green-50 border border-green-200 rounded-lg">
                        <svg class="w-4 h-4 text-green-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-sm text-green-700">Dokumen sudah diunggah.</span>
                    </div>
                @else
                    <p class="text-sm text-gray-400">Belum ada dokumen diunggah.</p>
                @endif
            </div>

            <div>
                <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">
                    {{ $application->mcuResult?->dokumen_path ? 'Ganti Dokumen MCU' : 'Unggah Dokumen MCU' }}
                </h2>

                <form method="POST" action="{{ route('kandidat.mcu.upload.store', $application->token) }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-4">
                        <label class="block text-sm text-gray-700 mb-2">
                            Pilih file PDF hasil MCU Anda
                        </label>
                        <input
                            type="file"
                            name="dokumen"
                            accept=".pdf"
                            class="block w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border file:border-gray-200 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-50"
                        >
                        @error('dokumen')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-400 mt-1">Format: PDF. Ukuran maksimal: 3 MB.</p>
                    </div>

                    <button
                        type="submit"
                        class="px-5 py-2.5 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors ease-out duration-150 cursor-pointer"
                    >
                        Unggah Dokumen
                    </button>
                </form>
            </div>

        </div>
    </div>

</x-layouts.public>
