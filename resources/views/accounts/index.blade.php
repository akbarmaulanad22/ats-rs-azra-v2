<x-layouts.app title="Akun Pengguna - ATS RS Azra">

    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Akun Pengguna</h1>
            <p class="text-xs text-gray-500 mt-0.5">Manajemen akun login karyawan RS Azra</p>
        </div>
        <a
            href="{{ route('akun.create') }}"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors ease-out duration-150"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Buat Akun
        </a>
    </div>

    @php
        $activeFilters = collect(['role', 'status'])->filter(fn ($k) => request($k))->count();
    @endphp

    <div class="mb-3" x-data="{ open: {{ $activeFilters > 0 ? 'true' : 'false' }} }">
        <form method="GET" action="{{ route('akun.index') }}">

            <div class="flex flex-wrap items-center gap-2 mb-2">
                <div class="relative flex-1 min-w-52">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Cari nama, NIP, atau username..."
                        class="w-full pl-8 pr-3 py-1.5 text-sm border border-gray-200 rounded-md focus-ring bg-white placeholder:text-gray-400"
                    >
                </div>

                <button
                    type="button"
                    @click="open = !open"
                    class="inline-flex items-center gap-1 px-3 py-1.5 text-sm border rounded-md transition-colors ease-out duration-150 cursor-pointer bg-white"
                    :class="open ? 'border-primary/40 text-primary bg-primary/5' : 'border-gray-200 text-gray-500 hover:border-primary/40 hover:text-primary'"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Filter
                    @if ($activeFilters > 0)
                        <span class="inline-flex items-center justify-center w-3.5 h-3.5 text-[9px] font-bold bg-primary text-white rounded-full">{{ $activeFilters }}</span>
                    @endif
                    <svg class="w-3 h-3 transition-transform ease-out duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <button type="submit" class="px-3.5 py-1.5 bg-primary text-white text-sm font-medium rounded-md hover:bg-primary-dark transition-colors ease-out duration-150 cursor-pointer">
                    Cari
                </button>

                @if (request()->hasAny(['q', 'role', 'status']))
                    <a href="{{ route('akun.index') }}" class="py-1.5 text-xs text-gray-400 hover:text-gray-600 transition-colors ease-out duration-150">
                        Reset
                    </a>
                @endif
            </div>

            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-1"
                class="grid grid-cols-2 md:grid-cols-4 gap-2.5"
            >
                <x-autocomplete-select
                    name="role"
                    label="Role"
                    :options="collect($roles)->map(fn ($r) => ['id' => $r->value, 'label' => $r->label()])"
                    :value="request('role')"
                    placeholder="Semua Role"
                    label-class="block text-[10px] font-medium text-gray-700 uppercase tracking-wide mb-1"
                />

                <x-autocomplete-select
                    name="status"
                    label="Status"
                    :options="[['id' => 'aktif', 'label' => 'Aktif'], ['id' => 'nonaktif', 'label' => 'Nonaktif']]"
                    :value="request('status')"
                    placeholder="Semua Status"
                    label-class="block text-[10px] font-medium text-gray-700 uppercase tracking-wide mb-1"
                />
            </div>

        </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-primary border-b border-primary/10 text-white">
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider">Karyawan</th>
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider w-36">Username</th>
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider w-36">Role</th>
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider w-24">Status</th>
                        <th class="w-24 px-3 py-2.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($accounts as $account)
                        <tr class="odd:bg-white even:bg-primary/5 hover:bg-primary/10 transition-colors ease-out duration-100">
                            <td class="px-3 py-1.5">
                                @if ($account->employee)
                                    <p class="text-xs font-medium text-gray-800 leading-tight">{{ $account->employee->nama_karyawan }}</p>
                                    <p class="text-[11px] text-gray-400 font-mono tabular-nums">{{ $account->employee->nip }}</p>
                                @else
                                    <span class="text-xs text-gray-400 italic">Tidak terhubung ke karyawan</span>
                                @endif
                            </td>
                            <td class="px-3 py-1.5 font-mono text-xs text-gray-600">{{ $account->username }}</td>
                            <td class="px-3 py-1.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-primary/10 text-primary font-medium">
                                    {{ $account->role->label() }}
                                </span>
                            </td>
                            <td class="px-3 py-1.5">
                                @if ($account->is_active)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs bg-green-50 text-green-700 font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-500 font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-3 py-1.5">
                                <div class="flex items-center justify-end gap-0.5">
                                    <a
                                        href="{{ route('akun.edit', $account) }}"
                                        class="p-1.5 rounded text-amber-400/60 hover:text-amber-500 hover:bg-amber-50 transition-colors ease-out duration-150"
                                        title="Edit akun"
                                        aria-label="Edit akun {{ $account->username }}"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('akun.toggle-aktif', $account) }}" onsubmit="return confirm('{{ $account->is_active ? 'Nonaktifkan akun ' . $account->username . '?' : 'Aktifkan kembali akun ' . $account->username . '?' }}')">
                                        @csrf
                                        @method('PATCH')
                                        <button
                                            type="submit"
                                            class="p-1.5 rounded transition-colors ease-out duration-150 cursor-pointer {{ $account->is_active ? 'text-red-400/60 hover:text-red-500 hover:bg-red-50' : 'text-green-400/60 hover:text-green-600 hover:bg-green-50' }}"
                                            title="{{ $account->is_active ? 'Nonaktifkan akun' : 'Aktifkan akun' }}"
                                            aria-label="{{ $account->is_active ? 'Nonaktifkan' : 'Aktifkan' }} akun {{ $account->username }}"
                                        >
                                            @if ($account->is_active)
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                </svg>
                                            @else
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            @endif
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-14 text-center">
                                <div class="flex flex-col items-center gap-2.5 max-w-xs mx-auto">
                                    <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                                        </svg>
                                    </div>
                                    @if (request()->hasAny(['q', 'role', 'status']))
                                        <div>
                                            <p class="text-sm font-medium text-gray-700">Tidak ada hasil</p>
                                            <p class="text-xs text-gray-400 mt-0.5">Coba ubah filter atau kata kunci pencarian</p>
                                        </div>
                                        <a href="{{ route('akun.index') }}" class="text-xs text-primary hover:text-primary-dark transition-colors ease-out duration-150">
                                            Reset filter
                                        </a>
                                    @else
                                        <div>
                                            <p class="text-sm font-medium text-gray-700">Belum ada akun pengguna</p>
                                            <p class="text-xs text-gray-400 mt-0.5">Buat akun login untuk karyawan RS Azra</p>
                                        </div>
                                        <a
                                            href="{{ route('akun.create') }}"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors ease-out duration-150"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            Buat Akun
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($accounts->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $accounts->links() }}
            </div>
        @endif
    </div>

</x-layouts.app>
