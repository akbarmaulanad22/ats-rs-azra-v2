{{-- Aksi Tahap: Wawancara --}}
{{-- Variables: $application, $lowongan, $currentStage, $assignedTemplates, $assignedReadinessTemplates, $priorInterviews, $eligibleInterviewers --}}

@if ($errors->any())
    <div class="mb-4 px-4 py-2.5 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
        {{ $errors->first() }}
    </div>
@endif

{{-- Prior interview results --}}
@if ($priorInterviews->isNotEmpty())
    <div class="bg-white rounded-xl border border-gray-100 p-5 mb-4">
        <h2 class="text-sm font-semibold text-gray-800 mb-3">Hasil Wawancara Sebelumnya</h2>
        <div class="space-y-4">
            @foreach ($priorInterviews as $priorStage)
                @php
                    $priorResult = $priorStage->interviewResult;
                    $stageLabels = [
                        'wawancara_user' => 'Wawancara User',
                        'wawancara_manajer_hr' => 'Wawancara Manajer HR',
                        'wawancara_direktur' => 'Wawancara Direktur',
                    ];
                    $priorBadge = match ($priorResult->keputusan) {
                        'lulus' => 'bg-green-100 text-green-700',
                        'gagal' => 'bg-red-100 text-red-600',
                        default => 'bg-amber-100 text-amber-700',
                    };
                    $priorLabel = match ($priorResult->keputusan) {
                        'lulus' => 'Lulus', 'gagal' => 'Gagal', default => 'Ditangguhkan',
                    };
                @endphp
                <div class="border-l-2 border-primary/30 pl-3">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs font-semibold text-gray-800">{{ $stageLabels[$priorStage->key] ?? $priorStage->key }}</p>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium {{ $priorBadge }}">
                            {{ $priorLabel }}
                        </span>
                    </div>
                    @if ($priorResult->ratings->isNotEmpty())
                        <div class="space-y-2 mb-2">
                            @foreach ($priorResult->ratings->groupBy('interview_template_id') as $templateId => $groupRatings)
                                @php $templateName = $groupRatings->first()->interviewTemplate?->nama ?? 'Template Dihapus'; @endphp
                                <div>
                                    <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wide mb-1">{{ $templateName }}</p>
                                    @foreach ($groupRatings as $rating)
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="text-gray-600">{{ $rating->nama_kriteria }}</span>
                                            <span class="font-medium text-gray-800">{{ $rating->nilai }}/5</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    @endif
                    @if ($priorResult->readinessAnswers->isNotEmpty())
                        <div class="space-y-2 mb-2">
                            @foreach ($priorResult->readinessAnswers->groupBy('interview_template_id') as $templateId => $groupAnswers)
                                @php $readinessTemplateName = $groupAnswers->first()->interviewTemplate?->nama ?? 'Template Dihapus'; @endphp
                                <div>
                                    <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wide mb-1">{{ $readinessTemplateName }}</p>
                                    @foreach ($groupAnswers as $answer)
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="text-gray-600">{{ $answer->pertanyaan }}</span>
                                            <span class="font-medium {{ $answer->jawaban ? 'text-green-600' : 'text-red-500' }}">
                                                {{ $answer->jawaban ? 'Ya' : 'Tidak' }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    @endif
                    @if ($priorResult->catatan)
                        <p class="text-xs text-gray-600 bg-gray-50 rounded px-2 py-1.5">{{ $priorResult->catatan }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif

@php
    $existingResult = $currentStage?->interviewResult;
    $hasSchedule = (bool) $currentStage?->jadwal;
    $isWawancaraUser = $currentStage?->key === 'wawancara_user';
@endphp

{{-- Phase 1: No schedule yet --}}
@if ($currentStage?->status->isAdvanceable() && !$hasSchedule)
    <div class="bg-white rounded-xl border border-gray-100 p-5">
        <h2 class="text-sm font-semibold text-gray-800 mb-4">Jadwalkan Wawancara</h2>
        <form action="{{ route('lowongan.wawancara.jadwal', [$lowongan, $application]) }}" method="POST">
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
                    <input type="text" name="lokasi" required placeholder="Ruang Meeting Lt. 3 / Link Google Meet"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40">
                    @error('lokasi')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                @if ($isWawancaraUser)
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Pewawancara <span class="text-red-500">*</span></label>
                        <select name="interviewer_id" required
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40">
                            <option value="">Pilih pewawancara...</option>
                            @foreach ($eligibleInterviewers as $interviewer)
                                <option value="{{ $interviewer->id }}" @if (old('interviewer_id') == $interviewer->id) selected @endif>
                                    {{ $interviewer->name }} ({{ $interviewer->role->label() }})
                                </option>
                            @endforeach
                        </select>
                        @error('interviewer_id')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endif
            </div>
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-4">
                <p class="text-xs text-amber-700">Keputusan wawancara baru dapat diberikan setelah jadwal ditetapkan dan form penilaian diisi.</p>
            </div>
            <button type="submit"
                class="w-full px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors ease-out duration-150 cursor-pointer">
                Simpan &amp; Kirim Undangan
            </button>
        </form>
    </div>

{{-- Phase 2: Schedule set, show interview form --}}
@elseif ($currentStage?->status->isAdvanceable() && $hasSchedule && !$existingResult)
    <div class="bg-white rounded-xl border border-gray-100 p-5 mb-4">
        <h2 class="text-sm font-semibold text-gray-800 mb-2">Jadwal Wawancara</h2>
        <dl class="space-y-1">
            <div>
                <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">Waktu</dt>
                <dd class="text-xs text-gray-800">{{ $currentStage->jadwal->translatedFormat('d M Y, H:i') }}</dd>
            </div>
            <div>
                <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">Lokasi</dt>
                <dd class="text-xs text-gray-800">{{ $currentStage->lokasi }}</dd>
            </div>
            @if ($isWawancaraUser && $currentStage->interviewer)
                <div>
                    <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">Pewawancara</dt>
                    <dd class="text-xs text-gray-800">{{ $currentStage->interviewer->name }}</dd>
                </div>
            @endif
        </dl>
    </div>

    {{-- Reschedule / Reassign form (HR Admin only) --}}
    @if (auth()->user()->isHrAdmin() && $isWawancaraUser)
        <div class="bg-white rounded-xl border border-gray-100 p-5 mb-4" x-data="{ open: false }">
            <button type="button" @click="open = !open"
                class="flex items-center gap-2 text-xs font-medium text-gray-500 hover:text-gray-700 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Ubah Jadwal / Pewawancara
            </button>
            <div x-show="open" x-collapse class="mt-4">
                <form action="{{ route('lowongan.wawancara.jadwal.update', [$lowongan, $application]) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="space-y-3 mb-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Tanggal & Waktu <span class="text-red-500">*</span></label>
                            <input type="datetime-local" name="jadwal" required
                                value="{{ $currentStage->jadwal->format('Y-m-d\TH:i') }}"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Lokasi <span class="text-red-500">*</span></label>
                            <input type="text" name="lokasi" required value="{{ $currentStage->lokasi }}"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Pewawancara</label>
                            @php
                                $rescheduleInterviewers = \App\Models\User::where('is_active', true)
                                    ->whereIn('role', [\App\Enums\Role::UnitHead->value, \App\Enums\Role::Employee->value])
                                    ->whereHas('employee', fn ($q) => $q->where('unit', $lowongan->unit->nama))
                                    ->get();
                            @endphp
                            <select name="interviewer_id"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40">
                                <option value="">Tetap sama</option>
                                @foreach ($rescheduleInterviewers as $interviewer)
                                    <option value="{{ $interviewer->id }}" @if ($currentStage->interviewer_id == $interviewer->id) selected @endif>
                                        {{ $interviewer->name }} ({{ $interviewer->role->label() }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button type="submit"
                        class="w-full px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-lg hover:bg-gray-900 transition-colors ease-out duration-150 cursor-pointer">
                        Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-100 p-5">
        <h2 class="text-sm font-semibold text-gray-800 mb-4">Penilaian Wawancara</h2>

        @if ($assignedTemplates->isEmpty())
            <div class="text-center py-4">
                <p class="text-sm text-gray-500">Belum ada kriteria, hubungi HR Admin.</p>
            </div>
        @else
            <form
                method="POST"
                action="{{ route('lowongan.wawancara.keputusan', [$lowongan, $application]) }}"
                x-data="{ keputusan: '{{ old('keputusan') }}' }"
            >
                @csrf

                @php $ratingIndex = 0; @endphp
                @foreach ($assignedTemplates as $template)
                    <div class="mb-4 space-y-3">
                        <p class="text-[10px] font-medium text-gray-700 uppercase tracking-wide">{{ $template->nama }}</p>
                        @foreach ($template->items as $item)
                            <div>
                                <label class="block text-xs text-gray-700 mb-1">
                                    {{ $item->teks }}
                                    <span class="text-red-500">*</span>
                                </label>
                                <input type="hidden" name="ratings[{{ $ratingIndex }}][interview_template_id]" value="{{ $template->id }}">
                                <input type="hidden" name="ratings[{{ $ratingIndex }}][nama_kriteria]" value="{{ $item->teks }}">
                                <div class="flex items-center gap-2">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <label class="flex flex-col items-center cursor-pointer">
                                            <input
                                                type="radio"
                                                name="ratings[{{ $ratingIndex }}][nilai]"
                                                value="{{ $i }}"
                                                class="sr-only peer"
                                                @if (old("ratings.{$ratingIndex}.nilai") == $i) checked @endif
                                                required
                                            >
                                            <span class="w-8 h-8 flex items-center justify-center text-sm font-semibold rounded-full border border-gray-200 peer-checked:bg-primary peer-checked:text-white peer-checked:border-primary hover:border-primary/40 transition-colors cursor-pointer">
                                                {{ $i }}
                                            </span>
                                        </label>
                                    @endfor
                                </div>
                                @error("ratings.{$ratingIndex}.nilai")
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            @php $ratingIndex++ @endphp
                        @endforeach
                    </div>
                @endforeach

                @if ($assignedReadinessTemplates->isNotEmpty())
                    <div class="mb-4">
                        <p class="text-xs font-semibold text-gray-700 mb-3">Pertanyaan Kesiapan</p>
                        @php $readinessIndex = 0; @endphp
                        @foreach ($assignedReadinessTemplates as $readinessTemplate)
                            <div class="mb-3 space-y-2">
                                <p class="text-[10px] font-medium text-gray-700 uppercase tracking-wide">{{ $readinessTemplate->nama }}</p>
                                @foreach ($readinessTemplate->items as $readinessItem)
                                    <div>
                                        <label class="block text-xs text-gray-700 mb-1">
                                            {{ $readinessItem->teks }}
                                            <span class="text-red-500">*</span>
                                        </label>
                                        <input type="hidden" name="readiness_answers[{{ $readinessIndex }}][interview_template_id]" value="{{ $readinessTemplate->id }}">
                                        <input type="hidden" name="readiness_answers[{{ $readinessIndex }}][pertanyaan]" value="{{ $readinessItem->teks }}">
                                        <div class="flex items-center gap-4">
                                            <label class="flex items-center gap-1.5 cursor-pointer">
                                                <input
                                                    type="radio"
                                                    name="readiness_answers[{{ $readinessIndex }}][jawaban]"
                                                    value="1"
                                                    class="w-4 h-4 accent-primary"
                                                    @if (old("readiness_answers.{$readinessIndex}.jawaban") === '1') checked @endif
                                                    required
                                                >
                                                <span class="text-xs text-gray-700">Ya</span>
                                            </label>
                                            <label class="flex items-center gap-1.5 cursor-pointer">
                                                <input
                                                    type="radio"
                                                    name="readiness_answers[{{ $readinessIndex }}][jawaban]"
                                                    value="0"
                                                    class="w-4 h-4 accent-primary"
                                                    @if (old("readiness_answers.{$readinessIndex}.jawaban") === '0') checked @endif
                                                    required
                                                >
                                                <span class="text-xs text-gray-700">Tidak</span>
                                            </label>
                                        </div>
                                    </div>
                                    @php $readinessIndex++ @endphp
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                @endif

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
                        rows="3"
                        placeholder="Catatan penilaian wawancara..."
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40 resize-none placeholder:text-gray-400"
                    >{{ old('catatan') }}</textarea>
                </div>

                <button
                    type="submit"
                    class="w-full px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors ease-out duration-150 disabled:opacity-50 cursor-pointer"
                    x-bind:disabled="!keputusan"
                >
                    Simpan Keputusan
                </button>
            </form>
        @endif
    </div>

{{-- Read-only: result already recorded --}}
@elseif ($existingResult)
    <div class="bg-white rounded-xl border border-gray-100 p-5">
        <h2 class="text-sm font-semibold text-gray-800 mb-3">Hasil Wawancara</h2>

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
            $resultBadge = match ($existingResult->keputusan) {
                'lulus' => ['bg-green-100 text-green-700', 'Diloloskan'],
                'gagal' => ['bg-red-100 text-red-600', 'Ditolak'],
                'reserved' => ['bg-amber-100 text-amber-700', 'Ditangguhkan'],
                default => ['bg-gray-100 text-gray-500', $existingResult->keputusan],
            };
        @endphp
        <div class="text-center py-2 mb-3">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $resultBadge[0] }}">
                {{ $resultBadge[1] }}
            </span>
        </div>

        @if ($existingResult->ratings->isNotEmpty())
            <div class="space-y-2 mb-3">
                @foreach ($existingResult->ratings as $rating)
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-600">{{ $rating->nama_kriteria }}</span>
                        <span class="text-xs font-semibold text-gray-900">{{ $rating->nilai }}/5</span>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($existingResult->catatan)
            <p class="text-xs text-gray-600 bg-gray-50 rounded-lg px-3 py-2">{{ $existingResult->catatan }}</p>
        @endif
    </div>

@elseif ($currentStage?->status === \App\Enums\ApplicationStageStatus::Gagal)
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-center">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-600">Ditolak</span>
    </div>
@endif
