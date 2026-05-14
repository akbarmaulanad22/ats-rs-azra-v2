<x-layouts.app title="Template Alur Kerja - ATS RS Azra">

    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Template Alur Kerja</h1>
            <p class="text-xs text-gray-500 mt-0.5">Kelola template tahapan rekrutmen</p>
        </div>
        <a
            href="{{ route('template-alur.create') }}"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors ease-out duration-150"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Buat Template
        </a>
    </div>

    @if (session('status'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg">
            {{ session('status') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-800 text-sm rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    @if ($templates->isEmpty())
        <div class="bg-white rounded-xl border border-gray-100 px-4 py-14 text-center">
            <div class="flex flex-col items-center gap-2.5 max-w-xs mx-auto">
                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-700">Belum ada template</p>
                    <p class="text-xs text-gray-400 mt-0.5">Buat template alur kerja rekrutmen pertama Anda</p>
                </div>
                <a
                    href="{{ route('template-alur.create') }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors ease-out duration-150"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Buat Template
                </a>
            </div>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($templates as $template)
                <div class="bg-white rounded-xl border border-gray-100 p-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <h2 class="text-sm font-semibold text-gray-900">{{ $template->nama }}</h2>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $template->stages->count() }} tahap</p>

                            <div class="mt-2.5 flex flex-wrap gap-1.5">
                                @foreach ($template->stages as $stage)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium
                                        @if ($stage->is_locked_first || $stage->is_locked_last)
                                            bg-primary/15 text-primary
                                        @else
                                            bg-gray-100 text-gray-600
                                        @endif
                                    ">
                                        {{ $stage->nama }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex items-center gap-0.5 flex-shrink-0">
                            <a
                                href="{{ route('template-alur.edit', $template) }}"
                                class="p-1.5 rounded text-amber-400/60 hover:text-amber-500 hover:bg-amber-50 transition-colors ease-out duration-150"
                                title="Edit template"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('template-alur.destroy', $template) }}" onsubmit="return confirm('Hapus template {{ addslashes($template->nama) }}?')">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="p-1.5 rounded text-red-400/60 hover:text-red-500 hover:bg-red-50 transition-colors ease-out duration-150 cursor-pointer"
                                    title="Hapus template"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</x-layouts.app>
