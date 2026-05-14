<x-layouts.app title="{{ $template->name }} - ATS RS Azra">

    <div class="mb-4">
        <a href="{{ route('alur-rekrutmen.index') }}" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-primary transition-colors ease-out duration-150 mb-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Template Alur Rekrutmen
        </a>
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-900">{{ $template->name }}</h1>
            <a
                href="{{ route('alur-rekrutmen.edit', $template) }}"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors ease-out duration-150"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
                Edit Template
            </a>
        </div>
        @if ($template->description)
            <p class="text-xs text-gray-500 mt-1">{{ $template->description }}</p>
        @endif
    </div>

    <div class="bg-white rounded-xl border border-gray-100 p-5">
        <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-4">
            Tahapan Rekrutmen ({{ $template->stages->count() }} tahap)
        </p>
        <div class="space-y-2">
            @foreach ($template->stages as $index => $stage)
                <div class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-primary/5 border border-primary/15">
                    <span class="flex-shrink-0 w-6 h-6 rounded-full bg-primary text-white flex items-center justify-center text-[11px] font-bold">
                        {{ $index + 1 }}
                    </span>
                    <span class="flex-1 text-xs font-medium text-gray-800">{{ $stage->label }}</span>
                    @if ($stage->is_locked_first)
                        <span class="text-[10px] text-primary/60 font-medium px-2 py-0.5 bg-primary/10 rounded">Pertama</span>
                    @endif
                    @if ($stage->is_locked_last)
                        <span class="text-[10px] text-primary/60 font-medium px-2 py-0.5 bg-primary/10 rounded">Terakhir</span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

</x-layouts.app>
