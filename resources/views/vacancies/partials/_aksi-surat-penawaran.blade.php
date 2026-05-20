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
        $offering = $application->offeringLetter;
        $hasSent = $offering?->sent_at;
        $offeringStatus = $offering?->status;

        $statusBadge = match (true) {
            $offeringStatus?->value === 'accepted' => ['bg-green-100 text-green-700', 'Diterima Kandidat'],
            $offeringStatus?->value === 'rejected' => ['bg-red-100 text-red-600', 'Ditolak Kandidat'],
            $hasSent !== null && $offeringStatus?->value === 'pending' => ['bg-amber-100 text-amber-700', 'Menunggu Respon Kandidat'],
            $currentStage->status->value === 'gagal' => ['bg-red-100 text-red-600', 'Gagal'],
            default => ['bg-blue-100 text-blue-700', 'Menunggu Pengiriman'],
        };
    @endphp
    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusBadge[0] }}">
        {{ $statusBadge[1] }}
    </span>
    @if ($offering?->sent_at)
        <p class="mt-2 text-xs text-gray-500">
            Terkirim: {{ $offering->sent_at->format('d M Y, H:i') }}
        </p>
    @endif
    @if ($offering?->responded_at)
        <p class="mt-1 text-xs text-gray-500">
            Direspon: {{ $offering->responded_at->format('d M Y, H:i') }}
        </p>
    @endif
</div>

{{-- Rejection reason --}}
@if ($offering?->status?->value === 'rejected' && $offering->rejection_reason)
    <div class="bg-red-50 rounded-xl border border-red-100 p-5 mb-4">
        <h2 class="text-sm font-semibold text-red-800 mb-2">Alasan Penolakan</h2>
        <p class="text-sm text-red-700">{{ $offering->rejection_reason }}</p>
    </div>
@endif

{{-- Offering form / read-only / waiting --}}
<div class="bg-white rounded-xl border border-gray-100 p-5">
    <h2 class="text-sm font-semibold text-gray-800 mb-4">Detail Penawaran</h2>

    @if (! $hasSent && $currentStage->status->isAdvanceable())
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
                        value="{{ old('jabatan_ditawarkan', $offering?->jabatan_ditawarkan) }}"
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
                        value="{{ old('gaji', $offering?->gaji) }}"
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
                        value="{{ old('tanggal_mulai', $offering?->tanggal_mulai?->format('Y-m-d')) }}"
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
                >{{ old('catatan', $offering?->catatan) }}</textarea>
                @error('catatan')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-4">
                <p class="text-xs text-amber-700">Setelah surat penawaran dikirim, kandidat akan menerima email berisi tautan untuk menerima atau menolak penawaran. Tautan berlaku selama 7 hari.</p>
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
                <dd class="text-sm text-gray-800 mt-0.5">{{ $offering?->jabatan_ditawarkan ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">Gaji</dt>
                <dd class="text-sm text-gray-800 mt-0.5">{{ $offering?->gaji ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">Tanggal Mulai Kerja</dt>
                <dd class="text-sm text-gray-800 mt-0.5">{{ $offering?->tanggal_mulai?->format('d M Y') ?? '—' }}</dd>
            </div>
        </dl>
        @if ($offering?->catatan)
            <div class="mt-3">
                <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">Catatan</dt>
                <dd class="text-sm text-gray-700 mt-1 bg-gray-50 rounded-lg px-3 py-2">{{ $offering->catatan }}</dd>
            </div>
        @endif
    @endif
</div>
