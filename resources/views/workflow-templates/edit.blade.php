<x-layouts.app title="Edit Template - ATS RS Azra">

    <div class="mb-4">
        <a href="{{ route('template-alur.index') }}" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-primary transition-colors ease-out duration-150 mb-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Template Alur Kerja
        </a>
        <h1 class="text-xl font-semibold text-gray-900">Edit Template Alur Kerja</h1>
    </div>

    <div
        class="bg-white/80 border border-gray-200 rounded-md"
        x-data="stageBuilder({{ $stages->toJson() }}, {{ $templateAlur->stages->pluck('id')->toJson() }})"
    >
        <form method="POST" action="{{ route('template-alur.update', $templateAlur) }}" @submit="prepareSubmit">
            @csrf
            @method('PUT')

            @include('workflow-templates._form')

            <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 flex justify-end gap-2">
                <a href="{{ route('template-alur.index') }}" class="px-3.5 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors ease-out duration-150">
                    Batal
                </a>
                <button type="submit" class="px-3.5 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors ease-out duration-150 cursor-pointer">
                    Perbarui Template
                </button>
            </div>
        </form>
    </div>

    @include('workflow-templates._builder-script')

</x-layouts.app>
