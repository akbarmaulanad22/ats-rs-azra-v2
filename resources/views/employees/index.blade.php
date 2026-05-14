<x-layouts.app title="Data Karyawan - ATS RS Azra">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 leading-tight">Data Karyawan</h1>
            <p class="text-xs text-gray-400 mt-1">Direktori karyawan RS Azra</p>
        </div>
        <a
            href="{{ route('karyawan.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors ease-out duration-150"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Karyawan
        </a>
    </div>

    {{-- Search & Filter --}}
    <div class="bg-white rounded-xl mb-4">
        <form method="GET" action="{{ route('karyawan.index') }}" class="flex flex-wrap items-center gap-2 px-4 py-3">
            <div class="relative flex-1 min-w-52">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Cari nama atau NIP..."
                    class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus-ring"
                >
            </div>

            <select name="unit" class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus-ring bg-white text-gray-700">
                <option value="">Semua Unit</option>
                @foreach ($filters['units'] as $unit)
                    <option value="{{ $unit }}" @selected(request('unit') === $unit)>{{ $unit }}</option>
                @endforeach
            </select>

            <select name="posisi" class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus-ring bg-white text-gray-700">
                <option value="">Semua Posisi</option>
                @foreach ($filters['posisi'] as $posisi)
                    <option value="{{ $posisi }}" @selected(request('posisi') === $posisi)>{{ $posisi }}</option>
                @endforeach
            </select>

            <select name="profesi" class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus-ring bg-white text-gray-700">
                <option value="">Semua Profesi</option>
                @foreach ($filters['profesi'] as $profesi)
                    <option value="{{ $profesi }}" @selected(request('profesi') === $profesi)>{{ $profesi }}</option>
                @endforeach
            </select>

            <select name="jabatan" class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus-ring bg-white text-gray-700">
                <option value="">Semua Jabatan</option>
                @foreach ($filters['jabatan'] as $jabatan)
                    <option value="{{ $jabatan }}" @selected(request('jabatan') === $jabatan)>{{ $jabatan }}</option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors ease-out duration-150">
                Cari
            </button>

            @if (request()->hasAny(['q', 'unit', 'posisi', 'profesi', 'jabatan']))
                <a href="{{ route('karyawan.index') }}" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 transition-colors ease-out duration-150">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl overflow-hidden">

        @if ($employees->total() > 0)
            <div class="px-5 py-2.5 border-b border-gray-50">
                <p class="text-xs text-gray-400">
                    Menampilkan <span class="text-gray-600 font-medium">{{ $employees->firstItem() }}–{{ $employees->lastItem() }}</span>
                    dari <span class="text-gray-600 font-medium">{{ $employees->total() }}</span> karyawan
                </p>
            </div>
        @endif

        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider w-36">NIP</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Nama Karyawan</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Unit</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Posisi</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Jabatan</th>
                    <th class="w-28 px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($employees as $employee)
                    <tr class="hover:bg-gray-50 transition-colors ease-out duration-100">
                        <td class="px-5 py-3.5 font-mono text-xs text-gray-400 tabular-nums">{{ $employee->nip }}</td>
                        <td class="px-5 py-3.5">
                            <a href="{{ route('karyawan.show', $employee) }}" class="font-medium text-gray-900 hover:text-primary transition-colors ease-out duration-150 leading-tight block">
                                {{ $employee->nama_karyawan }}
                            </a>
                            <span class="text-xs text-gray-400 mt-0.5 block">{{ $employee->profesi }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-gray-600">{{ $employee->unit }}</td>
                        <td class="px-5 py-3.5 text-gray-600">{{ $employee->posisi_pekerjaan }}</td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs bg-primary/10 text-primary font-medium">
                                {{ $employee->jabatan }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-0.5">
                                <a
                                    href="{{ route('karyawan.show', $employee) }}"
                                    class="p-1.5 rounded-md text-gray-400 hover:text-primary hover:bg-primary/10 transition-colors ease-out duration-150"
                                    title="Lihat detail"
                                    aria-label="Lihat detail {{ $employee->nama_karyawan }}"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a
                                    href="{{ route('karyawan.edit', $employee) }}"
                                    class="p-1.5 rounded-md text-gray-400 hover:text-primary hover:bg-primary/10 transition-colors ease-out duration-150"
                                    title="Edit karyawan"
                                    aria-label="Edit {{ $employee->nama_karyawan }}"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                </a>
                                <form method="POST" action="{{ route('karyawan.destroy', $employee) }}" onsubmit="return confirm('Hapus data karyawan ' + @js($employee->nama_karyawan) + '?')">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="p-1.5 rounded-md text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors ease-out duration-150"
                                        title="Hapus karyawan"
                                        aria-label="Hapus {{ $employee->nama_karyawan }}"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-16 text-center">
                            <div class="flex flex-col items-center gap-3 max-w-xs mx-auto">
                                <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                @if (request()->hasAny(['q', 'unit', 'posisi', 'profesi', 'jabatan']))
                                    <div>
                                        <p class="text-sm font-medium text-gray-700">Tidak ada hasil</p>
                                        <p class="text-xs text-gray-400 mt-1">Coba ubah filter atau kata kunci pencarian</p>
                                    </div>
                                    <a href="{{ route('karyawan.index') }}" class="text-sm text-primary hover:text-primary-dark transition-colors ease-out duration-150">
                                        Reset filter
                                    </a>
                                @else
                                    <div>
                                        <p class="text-sm font-medium text-gray-700">Belum ada data karyawan</p>
                                        <p class="text-xs text-gray-400 mt-1">Mulai tambahkan karyawan RS Azra</p>
                                    </div>
                                    <a
                                        href="{{ route('karyawan.create') }}"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors ease-out duration-150"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        Tambah Karyawan
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($employees->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $employees->links() }}
            </div>
        @endif
    </div>

</x-layouts.app>
