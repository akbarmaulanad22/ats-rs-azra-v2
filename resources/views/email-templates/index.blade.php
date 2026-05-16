<x-layouts.app title="Template Email - ATS RS Azra">

    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Template Email</h1>
            <p class="text-xs text-gray-500 mt-0.5">Kelola template email otomatis yang dikirim kepada kandidat dan pengguna internal</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Placeholder yang tersedia</p>
            <div class="flex flex-wrap gap-1.5 mt-2">
                @foreach (['{nama_kandidat}', '{judul_lowongan}', '{link_status}', '{link_tes}', '{tanggal_interview}', '{lokasi_interview}', '{tanggal_tenggat}', '{tanggal_onboarding}', '{nama_penerima}'] as $ph)
                    <code class="px-2 py-0.5 bg-gray-100 text-gray-700 rounded text-[11px] font-mono">{{ $ph }}</code>
                @endforeach
            </div>
        </div>

        <table class="w-full text-sm">
            <thead>
                <tr class="bg-primary border-b border-primary/10 text-white">
                    <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider w-48">Kunci</th>
                    <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider">Deskripsi</th>
                    <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider">Subjek</th>
                    <th class="px-3 py-2.5 w-20"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($templates as $template)
                    <tr class="odd:bg-white even:bg-primary/5 hover:bg-primary/10 transition-colors ease-out duration-100">
                        <td class="px-3 py-1.5">
                            <code class="text-xs font-mono text-primary bg-primary/5 px-1.5 py-0.5 rounded">{{ $template->key }}</code>
                        </td>
                        <td class="px-3 py-1.5 text-xs text-gray-600">{{ $template->deskripsi }}</td>
                        <td class="px-3 py-1.5 text-xs text-gray-700 max-w-xs truncate">{{ $template->subjek }}</td>
                        <td class="px-3 py-1.5">
                            <div class="flex items-center justify-end gap-0.5">
                                <a
                                    href="{{ route('template-email.edit', $template) }}"
                                    class="p-1.5 rounded text-amber-400/60 hover:text-amber-500 hover:bg-amber-50 transition-colors ease-out duration-150"
                                    title="Edit template"
                                    aria-label="Edit {{ $template->key }}"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</x-layouts.app>
