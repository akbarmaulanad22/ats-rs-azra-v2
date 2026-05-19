{{-- Aksi Tahap: MCU --}}
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

{{-- MCU status --}}
<div class="bg-white rounded-xl border border-gray-100 p-5 mb-4">
    <h2 class="text-sm font-semibold text-gray-800 mb-3">Status MCU</h2>
    @if ($application->mcuResult)
        @php
            $statusBadge = match ($application->mcuResult->status) {
                \App\Enums\McuStatus::Dijadwalkan => ['bg-blue-100 text-blue-700', 'Dijadwalkan'],
                \App\Enums\McuStatus::Selesai => ['bg-amber-100 text-amber-700', 'Selesai'],
                \App\Enums\McuStatus::Lulus => ['bg-green-100 text-green-700', 'Lulus'],
                \App\Enums\McuStatus::TidakLulus => ['bg-red-100 text-red-600', 'Tidak Lulus'],
            };
        @endphp
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusBadge[0] }}">
            {{ $statusBadge[1] }}
        </span>
        @if ($application->mcuResult->catatan)
            <p class="mt-2 text-xs text-gray-600 bg-gray-50 rounded-lg px-3 py-2">{{ $application->mcuResult->catatan }}</p>
        @endif
    @else
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-500">
            Belum diatur
        </span>
    @endif
</div>

{{-- MCU document --}}
<div class="bg-white rounded-xl border border-gray-100 p-5 mb-4">
    <h2 class="text-sm font-semibold text-gray-800 mb-3">Dokumen MCU</h2>

    @if ($application->mcuResult?->dokumen_path)
        <a
            href="{{ Storage::url($application->mcuResult->dokumen_path) }}"
            target="_blank"
            class="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-medium text-primary border border-primary/30 rounded-lg hover:bg-primary hover:text-white transition-colors ease-out duration-150 w-full justify-center mb-3"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Lihat Dokumen MCU
        </a>
    @else
        <p class="text-xs text-gray-400 mb-3">Belum ada dokumen diunggah.</p>
    @endif

    @if ($currentStage->status->isAdvanceable())
        <p class="text-[10px] text-gray-500 mb-2">Link upload untuk kandidat:</p>
        <input
            type="text"
            readonly
            value="{{ route('kandidat.mcu.upload', $application->token) }}"
            class="w-full px-2 py-1.5 text-xs border border-gray-200 rounded-lg bg-gray-50 text-gray-600 mb-3"
        >

        <form method="POST" action="{{ route('lowongan.mcu.dokumen', [$lowongan, $application]) }}" enctype="multipart/form-data">
            @csrf
            <label class="block text-[10px] font-medium text-gray-700 uppercase tracking-wide mb-1">
                Unggah Dokumen (HR Admin)
            </label>
            <input
                type="file"
                name="dokumen"
                accept=".pdf"
                class="block w-full text-xs text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border file:border-gray-200 file:text-xs file:font-medium file:text-gray-700 hover:file:bg-gray-50"
            >
            @error('dokumen')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
            <p class="text-[10px] text-gray-400 mt-1">PDF, maks. 3 MB</p>
            <button
                type="submit"
                class="mt-2 px-3 py-1.5 bg-gray-700 text-white text-xs font-medium rounded-lg hover:bg-gray-800 transition-colors ease-out duration-150 cursor-pointer"
            >
                Unggah
            </button>
        </form>
    @endif
</div>

{{-- Status update form --}}
<div class="bg-white rounded-xl border border-gray-100 p-5">
    <h2 class="text-sm font-semibold text-gray-800 mb-4">Perbarui Status MCU</h2>

    @if ($currentStage->status->isAdvanceable())
        <form
            method="POST"
            action="{{ route('lowongan.mcu.status', [$lowongan, $application]) }}"
            x-data="{ status: '{{ old('status', $application->mcuResult?->status?->value ?? 'dijadwalkan') }}' }"
        >
            @csrf

            <div class="space-y-2 mb-4">
                @foreach ([
                    'dijadwalkan' => ['Dijadwalkan', 'bg-blue-50 border-blue-300 text-blue-700'],
                    'selesai' => ['Selesai', 'bg-amber-50 border-amber-300 text-amber-700'],
                    'lulus' => ['Lulus', 'bg-green-50 border-green-300 text-green-700'],
                    'tidak_lulus' => ['Tidak Lulus', 'bg-red-50 border-red-300 text-red-700'],
                ] as $value => $config)
                    <label
                        class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-colors ease-out duration-150"
                        :class="status === '{{ $value }}' ? '{{ $config[1] }}' : 'border-gray-200 hover:border-gray-300'"
                    >
                        <input
                            type="radio"
                            name="status"
                            value="{{ $value }}"
                            x-model="status"
                            class="w-4 h-4"
                        >
                        <span class="text-sm font-medium" :class="status === '{{ $value }}' ? '' : 'text-gray-700'">{{ $config[0] }}</span>
                    </label>
                @endforeach
            </div>

            @error('status')
                <p class="text-xs text-red-600 mb-3">{{ $message }}</p>
            @enderror

            <div class="mb-5">
                <label class="block text-[10px] font-medium text-gray-700 uppercase tracking-wide mb-1">
                    Catatan <span class="text-gray-400 normal-case font-normal">(opsional)</span>
                </label>
                <textarea
                    name="catatan"
                    rows="3"
                    placeholder="Keterangan tambahan..."
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40 resize-none placeholder:text-gray-400"
                >{{ old('catatan', $application->mcuResult?->catatan) }}</textarea>
            </div>

            <div
                x-show="status === 'lulus' || status === 'tidak_lulus'"
                class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-4 text-xs text-amber-700"
            >
                <template x-if="status === 'lulus'">
                    <span>Kandidat akan dilanjutkan ke tahap Onboarding dan tidak dapat dibatalkan.</span>
                </template>
                <template x-if="status === 'tidak_lulus'">
                    <span>Kandidat akan ditolak dari pipeline dan tidak dapat dibatalkan.</span>
                </template>
            </div>

            <button
                type="submit"
                class="px-5 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors ease-out duration-150 cursor-pointer"
            >
                Simpan Status
            </button>
        </form>
    @else
        @php
            $stageBadge = match ($currentStage->status->value) {
                'selesai' => ['bg-green-100 text-green-700', 'Selesai'],
                'gagal' => ['bg-red-100 text-red-600', 'Ditolak'],
                default => ['bg-gray-100 text-gray-500', $currentStage->status->value],
            };
        @endphp
        <div class="text-center py-4">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $stageBadge[0] }}">
                {{ $stageBadge[1] }}
            </span>
            <p class="mt-2 text-xs text-gray-400">Tahap MCU sudah selesai diproses.</p>
        </div>
    @endif
</div>
