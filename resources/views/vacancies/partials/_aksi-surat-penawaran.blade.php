{{-- Aksi Tahap: Surat Penawaran --}}
{{-- Variables: $application, $lowongan, $currentStage --}}

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

{{-- Status --}}
<div class="bg-white rounded-xl border border-gray-100 p-5 mb-4">
    <h2 class="text-sm font-semibold text-gray-800 mb-3">Status Tahap</h2>
    @php
        $statusBadge = match ($currentStage->status->value) {
            'aktif' => ['bg-blue-100 text-blue-700', 'Menunggu Pengiriman'],
            'selesai' => ['bg-green-100 text-green-700', 'Surat Terkirim'],
            'gagal' => ['bg-red-100 text-red-600', 'Ditolak'],
            default => ['bg-gray-100 text-gray-500', $currentStage->status->value],
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

{{-- Offering form / read-only --}}
<div class="bg-white rounded-xl border border-gray-100 p-5">
    <h2 class="text-sm font-semibold text-gray-800 mb-4">Detail Penawaran</h2>

    @if ($currentStage->status->isAdvanceable())
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
