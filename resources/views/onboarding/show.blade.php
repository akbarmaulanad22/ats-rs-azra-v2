<x-layouts.app title="Onboarding - {{ $application->candidate->nama_lengkap }} - ATS RS Azra">

    <div class="mb-5">
        <a href="{{ route('lowongan.pipeline', $lowongan) }}" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-primary transition-colors ease-out duration-150 mb-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Pipeline
        </a>
        <h1 class="text-xl font-semibold text-gray-900">Onboarding</h1>
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

        {{-- Left --}}
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

            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <h2 class="text-sm font-semibold text-gray-800 mb-3">Status Tahap</h2>
                @php
                    $statusBadge = match ($onboardingStage->status->value) {
                        'aktif' => ['bg-blue-100 text-blue-700', 'Aktif'],
                        'selesai' => ['bg-green-100 text-green-700', 'Selesai'],
                        default => ['bg-gray-100 text-gray-500', $onboardingStage->status->value],
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

        </div>

        {{-- Right: Actions --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Send invitation --}}
            @if ($onboardingStage->status->isAdvanceable())
                <div class="bg-white rounded-xl border border-gray-100 p-5">
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

        </div>
    </div>

</x-layouts.app>
