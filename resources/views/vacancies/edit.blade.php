<x-layouts.app title="Edit Lowongan - ATS RS Azra">

    <div class="mb-4">
        <a href="{{ route('lowongan.index') }}" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-primary transition-colors ease-out duration-150 mb-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Lowongan Kerja
        </a>
        <h1 class="text-xl font-semibold text-gray-900">Edit Lowongan</h1>
        <p class="text-xs text-gray-500 mt-0.5">{{ $lowongan->judul_posisi }}</p>
    </div>

    <div class="bg-white/80 border border-gray-200 rounded-md">
        <form method="POST" action="{{ route('lowongan.update', $lowongan) }}">
            @csrf
            @method('PUT')

            @include('vacancies._form')

            <div class="flex items-center gap-2 px-4 py-3 border-t border-gray-200 bg-gray-200/90 rounded-b-md">
                <button
                    type="submit"
                    class="px-4 py-1.5 bg-primary text-white text-xs font-medium rounded hover:bg-primary-dark transition-colors ease-out duration-150 cursor-pointer"
                >
                    Perbarui Lowongan
                </button>
                <a href="{{ route('lowongan.index') }}" class="px-4 py-1.5 text-xs text-gray-500 border border-gray-300 rounded bg-white hover:bg-gray-50 transition-colors ease-out duration-150">
                    Batal
                </a>
            </div>
        </form>
    </div>

</x-layouts.app>
