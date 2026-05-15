<x-layouts.public title="Lamar - {{ $vacancy->judul_posisi }} - RS Azra">

    <div class="mb-4">
        <a href="{{ route('karier.show', $vacancy) }}" class="inline-flex items-center gap-1.5 text-xs text-gray-400 hover:text-gray-600 transition-colors ease-out duration-150">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Detail Lowongan
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
        {{-- Header --}}
        <div class="bg-primary px-6 py-6">
            <h1 class="text-xl font-bold text-white mb-1">Lamar Posisi</h1>
            <p class="text-white/70 text-sm">{{ $vacancy->judul_posisi }} &mdash; {{ $vacancy->unit->nama }}</p>
        </div>

        <div class="px-6 py-6">
            <form method="POST" action="{{ route('karier.lamar.store', $vacancy) }}" enctype="multipart/form-data">
                @csrf

                <div class="space-y-4">
                    <div>
                        <label for="nama_lengkap" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                            Nama Lengkap <span class="text-red-400">*</span>
                        </label>
                        <input
                            id="nama_lengkap"
                            name="nama_lengkap"
                            type="text"
                            value="{{ old('nama_lengkap') }}"
                            required
                            placeholder="Nama lengkap sesuai KTP"
                            class="w-full rounded-lg border @error('nama_lengkap') border-red-400 bg-red-50 @else border-gray-200 bg-gray-50 @enderror transition-colors focus-ring px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-300"
                        >
                        @error('nama_lengkap')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                            Email <span class="text-red-400">*</span>
                        </label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            placeholder="contoh@email.com"
                            class="w-full rounded-lg border @error('email') border-red-400 bg-red-50 @else border-gray-200 bg-gray-50 @enderror transition-colors focus-ring px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-300"
                        >
                        @error('email')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="no_telepon" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                            Nomor Telepon <span class="text-red-400">*</span>
                        </label>
                        <input
                            id="no_telepon"
                            name="no_telepon"
                            type="text"
                            value="{{ old('no_telepon') }}"
                            required
                            placeholder="08xxxxxxxxxx"
                            class="w-full rounded-lg border @error('no_telepon') border-red-400 bg-red-50 @else border-gray-200 bg-gray-50 @enderror transition-colors focus-ring px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-300"
                        >
                        @error('no_telepon')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="cv" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                            CV / Resume <span class="text-red-400">*</span>
                        </label>
                        <input
                            id="cv"
                            name="cv"
                            type="file"
                            accept=".pdf"
                            required
                            class="w-full rounded-lg border @error('cv') border-red-400 bg-red-50 @else border-gray-200 bg-gray-50 @enderror transition-colors px-3.5 py-2.5 text-sm text-gray-900 file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-medium file:bg-primary/10 file:text-primary hover:file:bg-primary/20"
                        >
                        <p class="mt-1 text-xs text-gray-400">Format PDF, maksimal 5 MB</p>
                        @error('cv')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 pt-5 border-t border-gray-100 flex items-center justify-between gap-4">
                    <p class="text-xs text-gray-400">
                        Pastikan data yang Anda masukkan sudah benar sebelum mengirim.
                    </p>
                    <button
                        type="submit"
                        class="shrink-0 inline-flex items-center gap-2 px-5 py-2.5 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors ease-out duration-150"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Kirim Lamaran
                    </button>
                </div>
            </form>
        </div>
    </div>

</x-layouts.public>
