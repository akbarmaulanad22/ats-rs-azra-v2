<x-layouts.app title="Edit Lowongan - ATS RS Azra">

    <div class="flex items-center gap-3 mb-5">
        <a href="{{ route('lowongan.index') }}" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors ease-out duration-150">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Edit Lowongan</h1>
            <p class="text-xs text-gray-500 mt-0.5">{{ $lowongan->judul_posisi }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('lowongan.update', $lowongan) }}">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-xl border border-gray-100 p-5 space-y-4">
            @include('vacancies._form')
        </div>

        <div class="flex items-center justify-end gap-3 mt-4">
            <a href="{{ route('lowongan.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 transition-colors ease-out duration-150">
                Batal
            </a>
            <button type="submit" class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors ease-out duration-150 cursor-pointer">
                Perbarui Lowongan
            </button>
        </div>
    </form>

</x-layouts.app>
