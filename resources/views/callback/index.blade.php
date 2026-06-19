<x-layouts.app title="Panggil Kembali Kandidat - ATS RS Azra">

    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Panggil Kembali Kandidat</h1>
            <p class="text-xs text-gray-500 mt-0.5">
                Kandidat gagal periode sebelumnya untuk posisi {{ $lowongan->judul_posisi }}
            </p>
        </div>
        <a href="{{ route('lowongan.index') }}" class="text-xs text-gray-400 hover:text-gray-600 transition-colors ease-out duration-150">
            &larr; Kembali ke lowongan
        </a>
    </div>

    @if (session('status'))
        <div class="mb-4 px-4 py-2.5 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
            {{ session('status') }}
        </div>
    @endif

    @error('callback')
        <div class="mb-4 px-4 py-2.5 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg">
            {{ $message }}
        </div>
    @enderror

    <div class="flex items-center gap-2 mb-3">
        @if ($includeScreening)
            <a href="{{ route('callback.index', $lowongan) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm border border-primary/40 text-primary bg-primary/5 rounded-md transition-colors ease-out duration-150">
                Tampilkan semua tahap kegagalan
            </a>
            <span class="text-xs text-gray-400">Termasuk gagal di tahap skrining CV</span>
        @else
            <a href="{{ route('callback.index', ['lowongan' => $lowongan, 'screening' => 1]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm border border-gray-200 text-gray-500 hover:border-primary/40 hover:text-primary rounded-md transition-colors ease-out duration-150">
                Hanya lolos skrining CV
            </a>
            <span class="text-xs text-gray-400">Sembunyikan kegagalan di tahap skrining CV</span>
        @endif
    </div>

    <form method="POST" action="{{ route('callback.invite', $lowongan) }}">
        @csrf

        <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-primary border-b border-primary/10 text-white">
                            <th class="px-3 py-2.5 w-8">
                                <input type="checkbox" x-data @click="$root.querySelectorAll('input[name=\'candidate_ids[]\']:not(:disabled)').forEach(c => c.checked = $event.target.checked)">
                            </th>
                            <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider">Kandidat</th>
                            <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider">Gagal di tahap</th>
                            <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider">Periode sebelumnya</th>
                            <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider w-48">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($rows as $row)
                            @php($candidate = $row['application']->candidate)
                            <tr class="odd:bg-white even:bg-primary/5 hover:bg-primary/10 transition-colors ease-out duration-100">
                                <td class="px-3 py-1.5 text-center">
                                    <input type="checkbox" name="candidate_ids[]" value="{{ $candidate->id }}">
                                </td>
                                <td class="px-3 py-1.5">
                                    <span class="text-xs font-semibold text-gray-900">{{ $candidate->nama_lengkap }}</span>
                                    <span class="block text-[10px] text-gray-400 mt-0.5">{{ $candidate->email }}</span>
                                </td>
                                <td class="px-3 py-1.5 text-xs text-gray-600">{{ $row['failed_stage_label'] }}</td>
                                <td class="px-3 py-1.5 text-xs text-gray-600">{{ $row['application']->vacancy->judul_posisi }}</td>
                                <td class="px-3 py-1.5">
                                    <div class="flex flex-wrap items-center gap-1">
                                        @if ($row['responded'])
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-green-100 text-green-700">Sudah melamar</span>
                                        @elseif ($row['invited'])
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-blue-100 text-blue-700">Sudah diundang</span>
                                        @endif
                                        @if ($row['active_elsewhere'])
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-amber-100 text-amber-700">Aktif di lowongan lain</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-14 text-center">
                                    <p class="text-sm font-medium text-gray-700">Tidak ada kandidat untuk dipanggil kembali</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Belum ada kandidat gagal dari periode sebelumnya pada template ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($rows->isNotEmpty())
            <div class="flex justify-end mt-4">
                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors ease-out duration-150 cursor-pointer">
                    Kirim Undangan
                </button>
            </div>
        @endif
    </form>

</x-layouts.app>
