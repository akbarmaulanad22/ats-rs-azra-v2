<x-layouts.app title="{{ $employee->nama_karyawan }} - ATS RS Azra">

    <div class="mb-6">
        @can('viewAny', App\Models\Employee::class)
            <a href="{{ route('karyawan.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-primary transition-colors mb-3">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </a>
        @endcan
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 leading-tight">{{ $employee->nama_karyawan }}</h1>
                <p class="text-xs text-gray-400 mt-1 font-mono">NIP: {{ $employee->nip }}</p>
            </div>
            @can('update', $employee)
                <a
                    href="{{ route('karyawan.edit', $employee) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 border border-gray-200 text-gray-600 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    Edit
                </a>
            @endcan
        </div>
    </div>

    <div class="bg-white rounded-xl p-6 max-w-2xl">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">NIP</p>
                <p class="text-sm font-medium text-gray-800 font-mono">{{ $employee->nip }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Nama Karyawan</p>
                <p class="text-sm font-medium text-gray-800">{{ $employee->nama_karyawan }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Unit</p>
                <p class="text-sm font-medium text-gray-800">{{ $employee->unit }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Posisi Pekerjaan</p>
                <p class="text-sm font-medium text-gray-800">{{ $employee->posisi_pekerjaan }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Profesi</p>
                <p class="text-sm font-medium text-gray-800">{{ $employee->profesi }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Jabatan</p>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs bg-primary/10 text-primary font-medium">
                    {{ $employee->jabatan }}
                </span>
            </div>
        </div>
    </div>

</x-layouts.app>
