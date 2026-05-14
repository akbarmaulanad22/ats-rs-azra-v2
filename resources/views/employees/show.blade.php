<x-layouts.app title="{{ $employee->nama_karyawan }} - ATS RS Azra">

    <div class="mb-4">
        @can('viewAny', App\Models\Employee::class)
            <a href="{{ route('karyawan.index') }}" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-primary transition-colors ease-out duration-150 mb-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Data Karyawan
            </a>
        @endcan
    </div>

    <div class="bg-white/80 border border-gray-200 rounded-md overflow-hidden">

        {{-- Header --}}
        <div class="bg-gray-200/90 border-b border-gray-300/80 px-4 py-4 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                    <span class="text-sm font-bold text-primary">{{ strtoupper(substr($employee->nama_karyawan, 0, 1)) }}</span>
                </div>
                <div>
                    <h1 class="text-sm font-semibold text-gray-900 leading-tight">{{ $employee->nama_karyawan }}</h1>
                    <p class="text-[11px] text-gray-500 font-mono mt-0.5">NIP: {{ $employee->nip }}</p>
                    <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded text-[10px] bg-primary/10 text-primary font-medium">
                        {{ $employee->jabatan }}
                    </span>
                </div>
            </div>
            @can('update', $employee)
                <a
                    href="{{ route('karyawan.edit', $employee) }}"
                    class="inline-flex items-center gap-1 px-3 py-1.5 border border-gray-300 text-gray-600 text-xs font-medium rounded bg-white hover:bg-gray-50 transition-colors ease-out duration-150 shrink-0"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    Edit
                </a>
            @endcan
        </div>

        {{-- Body --}}
        <div class="bg-white/80 px-4 py-5">
            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-3">Informasi Pekerjaan</p>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <p class="text-[10px] font-medium text-gray-500 uppercase tracking-wider mb-1">Unit</p>
                    <p class="text-xs font-medium text-gray-800">{{ $employee->unit }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-medium text-gray-500 uppercase tracking-wider mb-1">Posisi</p>
                    <p class="text-xs font-medium text-gray-800">{{ $employee->posisi_pekerjaan }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-medium text-gray-500 uppercase tracking-wider mb-1">Profesi</p>
                    <p class="text-xs font-medium text-gray-800">{{ $employee->profesi }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-medium text-gray-500 uppercase tracking-wider mb-1">Jabatan</p>
                    <p class="text-xs font-medium text-gray-800">{{ $employee->jabatan }}</p>
                </div>
            </div>
        </div>

    </div>

</x-layouts.app>
