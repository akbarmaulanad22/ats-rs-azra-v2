<x-layouts.app title="Tambah Unit - ATS RS Azra">

    <div class="mb-4">
        <a href="{{ route('unit.index') }}" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-primary transition-colors ease-out duration-150 mb-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Data Unit
        </a>
        <h1 class="text-xl font-semibold text-gray-900">Tambah Unit</h1>
    </div>

    <div class="bg-white/80 border border-gray-200 rounded-md">
        <form method="POST" action="{{ route('unit.store') }}">
            @csrf
            @if(old('popup', request('popup')))
                <input type="hidden" name="popup" value="1">
            @endif

            <div class="px-4 pt-4 pb-5">
                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-3">Informasi Unit</p>
                <div class="space-y-3">
                    <div>
                        <label for="nama" class="block text-xs font-medium text-gray-700 mb-1">Nama Unit <span class="text-red-500">*</span></label>
                        <input
                            type="text"
                            id="nama"
                            name="nama"
                            value="{{ old('nama', request('prefill')) }}"
                            placeholder="Contoh: ICU, HR, Finance"
                            class="w-full px-2.5 py-1.5 text-xs border rounded bg-white focus-ring @error('nama') border-red-400 @else border-gray-200 @enderror"
                        >
                        @error('nama')
                            <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 px-4 py-3 border-t border-gray-200 bg-gray-200/90 rounded-b-md">
                <button
                    type="submit"
                    class="px-4 py-1.5 bg-primary text-white text-xs font-medium rounded hover:bg-primary-dark transition-colors ease-out duration-150 cursor-pointer"
                >
                    Simpan Unit
                </button>
                <a href="{{ route('unit.index') }}" class="px-4 py-1.5 text-xs text-gray-500 border border-gray-300 rounded bg-white hover:bg-gray-50 transition-colors ease-out duration-150">
                    Batal
                </a>
            </div>
        </form>
    </div>

</x-layouts.app>
