<x-layouts.app title="Edit Template Email - ATS RS Azra">

    <div class="mb-4">
        <a href="{{ route('template-email.index') }}" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-primary transition-colors ease-out duration-150 mb-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Template Email
        </a>
        <h1 class="text-xl font-semibold text-gray-900">Edit Template Email</h1>
        <p class="text-xs text-gray-500 mt-0.5">{{ $templateEmail->deskripsi }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2">
            <div class="bg-white/80 border border-gray-200 rounded-md">
                <form method="POST" action="{{ route('template-email.update', $templateEmail) }}">
                    @csrf
                    @method('PUT')

                    <div class="px-4 pt-4 pb-5 space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">
                                Kunci Template
                            </label>
                            <input
                                type="text"
                                value="{{ $templateEmail->key }}"
                                class="w-full px-2.5 py-1.5 text-xs border border-gray-200 rounded bg-gray-50 font-mono text-gray-500"
                                disabled
                            >
                        </div>

                        <div>
                            <label for="subjek" class="block text-xs font-medium text-gray-700 mb-1">
                                Subjek <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="subjek"
                                name="subjek"
                                value="{{ old('subjek', $templateEmail->subjek) }}"
                                class="w-full px-2.5 py-1.5 text-xs border rounded bg-white focus-ring @error('subjek') border-red-400 @else border-gray-200 @enderror"
                            >
                            @error('subjek')
                                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="isi" class="block text-xs font-medium text-gray-700 mb-1">
                                Isi Email <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                id="isi"
                                name="isi"
                                rows="14"
                                class="w-full px-2.5 py-1.5 text-xs border rounded bg-white focus-ring font-mono @error('isi') border-red-400 @else border-gray-200 @enderror"
                            >{{ old('isi', $templateEmail->isi) }}</textarea>
                            @error('isi')
                                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex items-center gap-2 px-4 py-3 border-t border-gray-200 bg-gray-200/90 rounded-b-md">
                        <button
                            type="submit"
                            class="px-4 py-1.5 bg-primary text-white text-xs font-medium rounded hover:bg-primary-dark transition-colors ease-out duration-150 cursor-pointer"
                        >
                            Simpan Perubahan
                        </button>
                        <a href="{{ route('template-email.index') }}" class="px-4 py-1.5 text-xs text-gray-500 border border-gray-300 rounded bg-white hover:bg-gray-50 transition-colors ease-out duration-150">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div>
            <div class="bg-white/80 border border-gray-200 rounded-md p-4">
                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-3">Placeholder Tersedia</p>
                <div class="space-y-1.5">
                    @foreach ([
                        '{nama_kandidat}' => 'Nama lengkap kandidat',
                        '{judul_lowongan}' => 'Judul posisi lowongan',
                        '{link_status}' => 'Tautan status lamaran',
                        '{link_tes}' => 'Tautan akses tes',
                        '{tanggal_interview}' => 'Tanggal & waktu wawancara',
                        '{lokasi_interview}' => 'Lokasi wawancara',
                        '{tanggal_tenggat}' => 'Tenggat lowongan',
                        '{tanggal_onboarding}' => 'Tanggal onboarding',
                        '{nama_penerima}' => 'Nama penerima (pengguna internal)',
                    ] as $ph => $desc)
                        <div>
                            <code class="text-[11px] font-mono text-primary bg-primary/5 px-1.5 py-0.5 rounded">{{ $ph }}</code>
                            <p class="text-[11px] text-gray-500 mt-0.5">{{ $desc }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

</x-layouts.app>
