{{-- Aksi Tahap: Skrining CV --}}
{{-- Variables: $application, $lowongan, $currentStage --}}

@if ($errors->any())
    <div class="mb-4 px-4 py-2.5 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
        {{ $errors->first() }}
    </div>
@endif

<div class="bg-white rounded-xl border border-gray-100 p-5">
    <h2 class="text-sm font-semibold text-gray-800 mb-3">Keputusan Skrining</h2>

    @if ($currentStage?->status->isAdvanceable())
        <form
            method="POST"
            action="{{ route('lowongan.skrining.keputusan', [$lowongan, $application]) }}"
            x-data="{ keputusan: '{{ old('keputusan') }}' }"
        >
            @csrf

            <div class="space-y-2 mb-4">
                @foreach (['lulus' => ['Lulus', 'bg-green-50 border-green-300 text-green-700'], 'reserved' => ['Tunda', 'bg-amber-50 border-amber-300 text-amber-700'], 'gagal' => ['Gagal', 'bg-red-50 border-red-300 text-red-700']] as $value => $config)
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
                    rows="4"
                    placeholder="Alasan keputusan, kekuatan atau kelemahan kandidat..."
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40 resize-none placeholder:text-gray-400"
                >{{ old('catatan') }}</textarea>
                @error('catatan')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                class="w-full px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors ease-out duration-150 disabled:opacity-50 cursor-pointer"
                x-bind:disabled="!keputusan"
            >
                Simpan Keputusan
            </button>
        </form>
    @else
        @php
            $statusBadge = match ($currentStage?->status?->value) {
                'selesai' => ['bg-green-100 text-green-700', 'Diloloskan'],
                'gagal' => ['bg-red-100 text-red-600', 'Ditolak'],
                'reserved' => ['bg-amber-100 text-amber-700', 'Ditangguhkan'],
                default => ['bg-gray-100 text-gray-500', $currentStage?->status?->label() ?? '—'],
            };
        @endphp
        <div class="text-center py-3">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusBadge[0] }}">
                {{ $statusBadge[1] }}
            </span>
            @if ($currentStage?->catatan)
                <p class="mt-3 text-xs text-gray-600 text-left bg-gray-50 rounded-lg px-3 py-2">
                    {{ $currentStage->catatan }}
                </p>
            @else
                <p class="mt-2 text-xs text-gray-400">Tidak ada catatan.</p>
            @endif
        </div>
    @endif
</div>
