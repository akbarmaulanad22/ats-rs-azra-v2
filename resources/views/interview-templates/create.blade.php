<x-layouts.app title="Buat Template Wawancara - ATS RS Azra">

    <div class="mb-5">
        <a href="{{ route('template-wawancara.index') }}" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-primary transition-colors ease-out duration-150 mb-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Template Wawancara
        </a>
        <h1 class="text-xl font-semibold text-gray-900">Buat Template Wawancara</h1>
    </div>

    @if ($errors->any())
        <div class="mb-4 px-4 py-2.5 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            <p class="font-medium mb-1">Terdapat kesalahan:</p>
            <ul class="list-disc list-inside text-xs space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div x-data="itemForm()" class="space-y-4 max-w-4xl">
        <form method="POST" action="{{ route('template-wawancara.store') }}" @submit="prepareSubmit($event)">
            @csrf

            <div class="bg-white rounded-xl border border-gray-100 p-6 mb-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Nama Template <span class="text-red-500">*</span></label>
                        <input type="text" name="nama" value="{{ old('nama') }}" required
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-primary/40"
                            placeholder="Contoh: Kriteria Umum - Kepala Unit">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Tipe <span class="text-red-500">*</span></label>
                        <select name="tipe" required
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-primary/40 bg-white">
                            <option value="">Pilih tipe...</option>
                            @foreach (\App\Enums\InterviewTemplateType::cases() as $type)
                                <option value="{{ $type->value }}" {{ old('tipe') === $type->value ? 'selected' : '' }}>{{ $type->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            @include('interview-templates._item-form')

            <div class="flex gap-3">
                <button type="submit"
                    class="px-4 py-2 text-sm font-medium bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                    Simpan Template
                </button>
                <a href="{{ route('template-wawancara.index') }}"
                    class="px-4 py-2 text-sm font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>

</x-layouts.app>
