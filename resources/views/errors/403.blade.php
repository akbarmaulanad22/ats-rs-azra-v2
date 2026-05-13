<x-layouts.guest title="Akses Ditolak - ATS RS Azra">
    <div class="bg-white rounded-lg shadow-md p-8 text-center">
        <div class="text-6xl font-bold text-red-500 mb-4">403</div>
        <h1 class="text-xl font-bold text-gray-800 mb-2">Akses Ditolak</h1>
        <p class="text-gray-600 mb-6">
            Anda tidak memiliki izin untuk mengakses halaman ini.
        </p>
        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800">
            Kembali ke Dashboard
        </a>
    </div>
</x-layouts.guest>
