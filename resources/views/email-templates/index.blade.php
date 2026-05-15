<x-layouts.app title="Template Email - ATS RS Azra">

    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Template Email</h1>
            <p class="text-xs text-gray-500 mt-0.5">Kelola template email otomatis yang dikirim kepada kandidat dan pengguna internal</p>
        </div>
    </div>

    <div class="bg-white/80 border border-gray-200 rounded-md overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Placeholder yang tersedia</p>
            <div class="flex flex-wrap gap-1.5 mt-2">
                @foreach (['{nama_kandidat}', '{judul_lowongan}', '{link_status}', '{link_tes}', '{tanggal_interview}', '{lokasi_interview}', '{tanggal_tenggat}', '{tanggal_onboarding}', '{nama_penerima}'] as $ph)
                    <code class="px-2 py-0.5 bg-gray-100 text-gray-700 rounded text-[11px] font-mono">{{ $ph }}</code>
                @endforeach
            </div>
        </div>

        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 uppercase tracking-wider w-48">Kunci</th>
                    <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                    <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 uppercase tracking-wider">Subjek</th>
                    <th class="px-4 py-2.5 w-20"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($templates as $template)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3">
                            <code class="text-xs font-mono text-primary bg-primary/5 px-1.5 py-0.5 rounded">{{ $template->key }}</code>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600">{{ $template->deskripsi }}</td>
                        <td class="px-4 py-3 text-xs text-gray-700 max-w-xs truncate">{{ $template->subjek }}</td>
                        <td class="px-4 py-3 text-right">
                            <a
                                href="{{ route('template-email.edit', $template) }}"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-primary border border-primary/30 rounded hover:bg-primary/5 transition-colors ease-out duration-150"
                            >
                                Edit
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</x-layouts.app>
