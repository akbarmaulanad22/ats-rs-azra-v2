{{-- Aksi Tahap: MCU (2-Phase) --}}
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

@php
    $mcuResult = $currentStage?->mcuResult;
    $hasSchedule = (bool) $currentStage?->jadwal;
@endphp

{{-- Phase 1: No schedule yet → scheduling form --}}
@if ($currentStage?->status->isAdvanceable() && !$hasSchedule)
    <div class="bg-white rounded-xl border border-gray-100 p-5">
        <h2 class="text-sm font-semibold text-gray-800 mb-4">Jadwalkan MCU</h2>
        <form action="{{ route('lowongan.mcu.jadwal', [$lowongan, $application]) }}" method="POST">
            @csrf
            <div class="space-y-3 mb-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Tanggal & Waktu <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="jadwal" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40">
                    @error('jadwal')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Lokasi <span class="text-red-500">*</span></label>
                    <input type="text" name="lokasi" required placeholder="RS Azra Lt. 2 / Laboratorium Klinik"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40">
                    @error('lokasi')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-4">
                <p class="text-xs text-amber-700">Keputusan MCU baru dapat diberikan setelah jadwal ditetapkan dan form penilaian diisi.</p>
            </div>
            <button type="submit"
                class="w-full px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors ease-out duration-150 cursor-pointer">
                Simpan &amp; Kirim Instruksi MCU
            </button>
        </form>
    </div>

{{-- Phase 2: Schedule set, no result yet → assessment form --}}
@elseif ($currentStage?->status->isAdvanceable() && $hasSchedule && !$mcuResult)
    <div class="bg-white rounded-xl border border-gray-100 p-5 mb-4">
        <h2 class="text-sm font-semibold text-gray-800 mb-2">Jadwal MCU</h2>
        <dl class="space-y-1">
            <div>
                <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">Waktu</dt>
                <dd class="text-xs text-gray-800">{{ $currentStage->jadwal->translatedFormat('d M Y, H:i') }}</dd>
            </div>
            <div>
                <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">Lokasi</dt>
                <dd class="text-xs text-gray-800">{{ $currentStage->lokasi }}</dd>
            </div>
        </dl>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 p-5">
        <h2 class="text-sm font-semibold text-gray-800 mb-4">Penilaian MCU</h2>
        <form
            method="POST"
            action="{{ route('lowongan.mcu.keputusan', [$lowongan, $application]) }}"
            enctype="multipart/form-data"
            x-data="{ keputusan: '{{ old('keputusan') }}' }"
        >
            @csrf

            <div class="mb-4">
                <label class="block text-[10px] font-medium text-gray-700 uppercase tracking-wide mb-1">
                    Dokumen MCU <span class="text-gray-400 normal-case font-normal">(opsional, PDF maks. 3 MB)</span>
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
            </div>

            <div class="space-y-2 mb-4">
                @foreach ([
                    'lulus' => ['Lulus', 'bg-green-50 border-green-300 text-green-700'],
                    'ditangguhkan' => ['Ditangguhkan', 'bg-amber-50 border-amber-300 text-amber-700'],
                    'tidak_lulus' => ['Tidak Lulus', 'bg-red-50 border-red-300 text-red-700'],
                ] as $value => $config)
                    <label
                        class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-colors ease-out duration-150"
                        :class="keputusan === '{{ $value }}' ? '{{ $config[1] }}' : 'border-gray-200 hover:border-gray-300'"
                    >
                        <input
                            type="radio"
                            name="keputusan"
                            value="{{ $value }}"
                            x-model="keputusan"
                            class="w-4 h-4 accent-current"
                            @if (old('keputusan') === $value) checked @endif
                        >
                        <span class="text-sm font-medium" :class="keputusan === '{{ $value }}' ? '' : 'text-gray-700'">{{ $config[0] }}</span>
                    </label>
                @endforeach
            </div>

            @error('keputusan')
                <p class="text-xs text-red-600 mb-3">{{ $message }}</p>
            @enderror

            <div class="mb-4">
                <label class="block text-[10px] font-medium text-gray-700 uppercase tracking-wide mb-1">
                    Catatan <span class="text-gray-400 normal-case font-normal">(opsional)</span>
                </label>
                <textarea
                    name="catatan"
                    rows="3"
                    placeholder="Catatan penilaian MCU..."
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40 resize-none placeholder:text-gray-400"
                >{{ old('catatan') }}</textarea>
            </div>

            <div
                x-show="keputusan === 'lulus' || keputusan === 'tidak_lulus'"
                class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-4 text-xs text-amber-700"
            >
                <template x-if="keputusan === 'lulus'">
                    <span>Kandidat akan dilanjutkan ke tahap Onboarding dan tidak dapat dibatalkan.</span>
                </template>
                <template x-if="keputusan === 'tidak_lulus'">
                    <span>Kandidat akan ditolak dari pipeline dan tidak dapat dibatalkan.</span>
                </template>
            </div>

            <button
                type="submit"
                class="w-full px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors ease-out duration-150 disabled:opacity-50 cursor-pointer"
                x-bind:disabled="!keputusan"
            >
                Simpan Keputusan
            </button>
        </form>
    </div>

{{-- Read-only: result already recorded --}}
@elseif ($mcuResult)
    <div class="bg-white rounded-xl border border-gray-100 p-5">
        <h2 class="text-sm font-semibold text-gray-800 mb-3">Hasil MCU</h2>

        @if ($currentStage?->jadwal)
            <dl class="space-y-1 mb-4">
                <div>
                    <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">Waktu</dt>
                    <dd class="text-xs text-gray-800">{{ $currentStage->jadwal->translatedFormat('d M Y, H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">Lokasi</dt>
                    <dd class="text-xs text-gray-800">{{ $currentStage->lokasi }}</dd>
                </div>
            </dl>
        @endif

        @php
            $resultBadge = match ($mcuResult->keputusan) {
                \App\Enums\McuStatus::Lulus => ['bg-green-100 text-green-700', 'Lulus'],
                \App\Enums\McuStatus::Ditangguhkan => ['bg-amber-100 text-amber-700', 'Ditangguhkan'],
                \App\Enums\McuStatus::TidakLulus => ['bg-red-100 text-red-600', 'Tidak Lulus'],
            };
        @endphp
        <div class="text-center py-2 mb-3">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $resultBadge[0] }}">
                {{ $resultBadge[1] }}
            </span>
        </div>

        @if ($mcuResult->dokumen_path)
            <a
                href="{{ Storage::url($mcuResult->dokumen_path) }}"
                target="_blank"
                class="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-medium text-primary border border-primary/30 rounded-lg hover:bg-primary hover:text-white transition-colors ease-out duration-150 w-full justify-center mb-3"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Lihat Dokumen MCU
            </a>
        @endif

        @if ($mcuResult->catatan)
            <p class="text-xs text-gray-600 bg-gray-50 rounded-lg px-3 py-2">{{ $mcuResult->catatan }}</p>
        @endif
    </div>

@elseif ($currentStage?->status === \App\Enums\ApplicationStageStatus::Gagal)
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-center">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-600">Ditolak</span>
    </div>
@endif
