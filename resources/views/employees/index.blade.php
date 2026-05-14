<x-layouts.app title="Data Karyawan - ATS RS Azra">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 leading-tight">Data Karyawan</h1>
            <p class="text-xs text-gray-400 mt-1">Direktori karyawan RS Azra</p>
        </div>
        <a
            href="{{ route('karyawan.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary/90 transition-colors"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Karyawan
        </a>
    </div>

    {{-- Search & Filter --}}
    <div class="bg-white rounded-xl p-4 mb-5">
        <form method="GET" action="{{ route('karyawan.index') }}" class="flex flex-wrap gap-3">
            <input
                type="text"
                name="q"
                value="{{ request('q') }}"
                placeholder="Cari nama atau NIP..."
                class="flex-1 min-w-48 px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
            >

            <select name="unit" class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary bg-white">
                <option value="">Semua Unit</option>
                @foreach ($filters['units'] as $unit)
                    <option value="{{ $unit }}" @selected(request('unit') === $unit)>{{ $unit }}</option>
                @endforeach
            </select>

            <select name="posisi" class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary bg-white">
                <option value="">Semua Posisi</option>
                @foreach ($filters['posisi'] as $posisi)
                    <option value="{{ $posisi }}" @selected(request('posisi') === $posisi)>{{ $posisi }}</option>
                @endforeach
            </select>

            <select name="profesi" class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary bg-white">
                <option value="">Semua Profesi</option>
                @foreach ($filters['profesi'] as $profesi)
                    <option value="{{ $profesi }}" @selected(request('profesi') === $profesi)>{{ $profesi }}</option>
                @endforeach
            </select>

            <select name="jabatan" class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary bg-white">
                <option value="">Semua Jabatan</option>
                @foreach ($filters['jabatan'] as $jabatan)
                    <option value="{{ $jabatan }}" @selected(request('jabatan') === $jabatan)>{{ $jabatan }}</option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary/90 transition-colors">
                Cari
            </button>

            @if (request()->hasAny(['q', 'unit', 'posisi', 'profesi', 'jabatan']))
                <a href="{{ route('karyawan.index') }}" class="px-4 py-2 text-sm text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wide">NIP</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wide">Nama Karyawan</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wide">Unit</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wide">Posisi</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wide">Profesi</th>
                    <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wide">Jabatan</th>
                    <th class="text-right px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wide">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($employees as $employee)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3.5 font-mono text-gray-500 text-xs">{{ $employee->nip }}</td>
                        <td class="px-5 py-3.5 font-medium text-gray-800">{{ $employee->nama_karyawan }}</td>
                        <td class="px-5 py-3.5 text-gray-600">{{ $employee->unit }}</td>
                        <td class="px-5 py-3.5 text-gray-600">{{ $employee->posisi_pekerjaan }}</td>
                        <td class="px-5 py-3.5 text-gray-600">{{ $employee->profesi }}</td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-primary/10 text-primary font-medium">
                                {{ $employee->jabatan }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('karyawan.show', $employee) }}" class="text-xs text-gray-500 hover:text-primary transition-colors px-2 py-1 rounded hover:bg-gray-100">
                                    Detail
                                </a>
                                <a href="{{ route('karyawan.edit', $employee) }}" class="text-xs text-gray-500 hover:text-primary transition-colors px-2 py-1 rounded hover:bg-gray-100">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('karyawan.destroy', $employee) }}" onsubmit="return confirm('Hapus data karyawan ' + @js($employee->nama_karyawan) + '?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-gray-500 hover:text-red-600 transition-colors px-2 py-1 rounded hover:bg-red-50">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-gray-400 text-sm">
                            Belum ada data karyawan.
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
