{{-- Aksi Tahap: Onboarding --}}
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
            'aktif' => ['bg-blue-100 text-blue-700', 'Aktif'],
            'selesai' => ['bg-green-100 text-green-700', 'Selesai'],
            default => ['bg-gray-100 text-gray-500', $currentStage->status->value],
        };
    @endphp
    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusBadge[0] }}">
        {{ $statusBadge[1] }}
    </span>
    @if ($application->onboardingResult?->sent_at)
        <p class="mt-2 text-xs text-gray-500">
            Undangan terkirim: {{ $application->onboardingResult->sent_at->format('d M Y, H:i') }}
        </p>
    @endif
    @if ($application->onboardingResult?->tanggal_bergabung)
        <p class="mt-1 text-xs text-gray-500">
            Tanggal bergabung: {{ $application->onboardingResult->tanggal_bergabung->format('d M Y') }}
        </p>
    @endif
</div>

@if ($currentStage->status->isAdvanceable())
    {{-- Send invitation --}}
    <div class="bg-white rounded-xl border border-gray-100 p-5 mb-4">
        <h2 class="text-sm font-semibold text-gray-800 mb-4">
            {{ $application->onboardingResult?->sent_at ? 'Kirim Ulang Undangan Onboarding' : 'Kirim Undangan Onboarding' }}
        </h2>

        <form method="POST" action="{{ route('lowongan.onboarding.undangan', [$lowongan, $application]) }}">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-[10px] font-medium text-gray-700 uppercase tracking-wide mb-1">
                        Tanggal Bergabung <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="date"
                        name="tanggal_bergabung"
                        value="{{ old('tanggal_bergabung', $application->onboardingResult?->tanggal_bergabung?->format('Y-m-d')) }}"
                        min="{{ now()->toDateString() }}"
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40"
                    >
                    @error('tanggal_bergabung')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-5">
                <label class="block text-[10px] font-medium text-gray-700 uppercase tracking-wide mb-1">
                    Catatan / Instruksi <span class="text-gray-400 normal-case font-normal">(opsional)</span>
                </label>
                <textarea
                    name="catatan"
                    rows="4"
                    placeholder="Instruksi untuk hari pertama kerja, dokumen yang perlu dibawa, dll..."
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40 resize-none placeholder:text-gray-400"
                >{{ old('catatan', $application->onboardingResult?->catatan) }}</textarea>
            </div>

            <button
                type="submit"
                class="px-5 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors ease-out duration-150 cursor-pointer"
            >
                Kirim Undangan
            </button>
        </form>
    </div>

    {{-- Mark complete --}}
    @if ($application->onboardingResult?->sent_at)
        <div class="bg-white rounded-xl border border-gray-100 p-5">
            <h2 class="text-sm font-semibold text-gray-800 mb-2">Selesaikan Onboarding</h2>
            <p class="text-xs text-gray-500 mb-4">Tandai onboarding sebagai selesai setelah kandidat bergabung. Tindakan ini akan menyelesaikan seluruh proses rekrutmen.</p>

            <form method="POST" action="{{ route('lowongan.onboarding.selesai', [$lowongan, $application]) }}">
                @csrf
                <button
                    type="submit"
                    class="px-5 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors ease-out duration-150 cursor-pointer"
                    onclick="return confirm('Tandai onboarding sebagai selesai? Tindakan ini tidak dapat dibatalkan.')"
                >
                    Tandai Selesai
                </button>
            </form>
        </div>
    @endif
@else
    <div class="bg-white rounded-xl border border-gray-100 p-5">
        <div class="text-center py-4">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-700">
                Onboarding Selesai
            </span>
            @if ($application->onboardingResult?->tanggal_bergabung)
                <p class="mt-2 text-sm text-gray-600">
                    Tanggal bergabung: {{ $application->onboardingResult->tanggal_bergabung->format('d M Y') }}
                </p>
            @endif
            <p class="mt-1 text-xs text-gray-400">Kandidat telah berhasil direkrut.</p>
        </div>
    </div>
@endif
