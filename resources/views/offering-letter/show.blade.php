<x-layouts.app title="Surat Penawaran - {{ $application->candidate->nama_lengkap }} - ATS RS Azra">

    <div class="mb-5">
        <a href="{{ route('lowongan.pipeline', $lowongan) }}" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-primary transition-colors ease-out duration-150 mb-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Pipeline
        </a>
        <h1 class="text-xl font-semibold text-gray-900">Surat Penawaran Kerja</h1>
        <p class="text-xs text-gray-500 mt-0.5">{{ $lowongan->judul_posisi }} &mdash; {{ $lowongan->unit->nama }}</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 px-4 py-2.5 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    @if (session('success'))
        <div class="mb-4 px-4 py-2.5 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Left: Candidate info --}}
        <div class="lg:col-span-1 space-y-4">

            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <h2 class="text-sm font-semibold text-gray-800 mb-3">Identitas Kandidat</h2>
                <dl class="space-y-2">
                    <div>
                        <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">Nama Lengkap</dt>
                        <dd class="text-sm font-semibold text-gray-900 mt-0.5">{{ $application->candidate->nama_lengkap }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">Email</dt>
                        <dd class="text-sm text-gray-700 mt-0.5">{{ $application->candidate->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">No. Telepon</dt>
                        <dd class="text-sm text-gray-700 mt-0.5">{{ $application->candidate->no_telepon }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Status panel --}}
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <h2 class="text-sm font-semibold text-gray-800 mb-3">Status Tahap</h2>
                @php
                    $statusBadge = match ($offeringStage->status->value) {
                        'aktif' => ['bg-blue-100 text-blue-700', 'Menunggu Pengiriman'],
                        'selesai' => ['bg-green-100 text-green-700', 'Surat Terkirim'],
                        'gagal' => ['bg-red-100 text-red-600', 'Ditolak'],
                        default => ['bg-gray-100 text-gray-500', $offeringStage->status->value],
                    };
                @endphp
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusBadge[0] }}">
                    {{ $statusBadge[1] }}
                </span>
                @if ($application->offeringLetter?->sent_at)
                    <p class="mt-2 text-xs text-gray-500">
                        Terkirim: {{ $application->offeringLetter->sent_at->format('d M Y, H:i') }}
                    </p>
                @endif
            </div>

        </div>

        {{-- Right: Offering form / summary --}}
        <div class="lg:col-span-2">

            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <h2 class="text-sm font-semibold text-gray-800 mb-4">Detail Penawaran</h2>

                @if ($offeringStage->status->isAdvanceable())
                    <form method="POST" action="{{ route('lowongan.surat-penawaran.kirim', [$lowongan, $application]) }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-[10px] font-medium text-gray-700 uppercase tracking-wide mb-1">
                                    Jabatan yang Ditawarkan <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="jabatan_ditawarkan"
                                    value="{{ old('jabatan_ditawarkan', $application->offeringLetter?->jabatan_ditawarkan) }}"
                                    placeholder="contoh: Perawat Rawat Inap"
                                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40 placeholder:text-gray-400"
                                >
                                @error('jabatan_ditawarkan')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-[10px] font-medium text-gray-700 uppercase tracking-wide mb-1">
                                    Gaji <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="gaji"
                                    value="{{ old('gaji', $application->offeringLetter?->gaji) }}"
                                    placeholder="contoh: Rp 5.000.000"
                                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40 placeholder:text-gray-400"
                                >
                                @error('gaji')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-[10px] font-medium text-gray-700 uppercase tracking-wide mb-1">
                                    Tanggal Mulai Kerja <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="date"
                                    name="tanggal_mulai"
                                    value="{{ old('tanggal_mulai', $application->offeringLetter?->tanggal_mulai?->format('Y-m-d')) }}"
                                    min="{{ now()->toDateString() }}"
                                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40"
                                >
                                @error('tanggal_mulai')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="block text-[10px] font-medium text-gray-700 uppercase tracking-wide mb-1">
                                Catatan <span class="text-gray-400 normal-case font-normal">(opsional)</span>
                            </label>
                            <textarea
                                name="catatan"
                                rows="4"
                                placeholder="Informasi tambahan untuk kandidat..."
                                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40 resize-none placeholder:text-gray-400"
                            >{{ old('catatan', $application->offeringLetter?->catatan) }}</textarea>
                            @error('catatan')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-4">
                            <p class="text-xs text-amber-700">Setelah surat penawaran dikirim, kandidat akan maju ke tahap MCU secara otomatis dan tidak dapat dibatalkan.</p>
                        </div>

                        <button
                            type="submit"
                            class="px-5 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors ease-out duration-150 cursor-pointer"
                        >
                            Kirim Surat Penawaran
                        </button>
                    </form>
                @else
                    {{-- Read-only view --}}
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3">
                        <div>
                            <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">Jabatan yang Ditawarkan</dt>
                            <dd class="text-sm text-gray-800 mt-0.5">{{ $application->offeringLetter?->jabatan_ditawarkan ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">Gaji</dt>
                            <dd class="text-sm text-gray-800 mt-0.5">{{ $application->offeringLetter?->gaji ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">Tanggal Mulai Kerja</dt>
                            <dd class="text-sm text-gray-800 mt-0.5">{{ $application->offeringLetter?->tanggal_mulai?->format('d M Y') ?? '—' }}</dd>
                        </div>
                    </dl>
                    @if ($application->offeringLetter?->catatan)
                        <div class="mt-3">
                            <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">Catatan</dt>
                            <dd class="text-sm text-gray-700 mt-1 bg-gray-50 rounded-lg px-3 py-2">{{ $application->offeringLetter->catatan }}</dd>
                        </div>
                    @endif
                @endif
            </div>

        </div>
    </div>

</x-layouts.app>
