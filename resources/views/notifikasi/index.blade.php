<x-layouts.app title="Notifikasi">
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <h1 class="text-xl font-bold text-gray-900">Notifikasi</h1>
            <p class="text-sm text-gray-500 mt-1">Semua notifikasi ditandai telah dibaca saat halaman ini dibuka.</p>
        </div>

        @if ($notifikasi->isEmpty())
            <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <p class="text-gray-500 text-sm">Tidak ada notifikasi.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($notifikasi as $item)
                    <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-start gap-4 {{ $item->read_at ? '' : 'border-primary/30 bg-primary/5' }}">
                        <div class="mt-0.5 shrink-0">
                            @if ($item->type === \App\Notifications\PenawaranDirespon::class)
                                @php
                                    $isAccepted = ($item->data['status'] ?? '') === 'accepted';
                                @endphp
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-full {{ $isAccepted ? 'bg-green-100' : 'bg-red-100' }}">
                                    @if ($isAccepted)
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    @endif
                                </span>
                            @elseif ($item->type === \App\Notifications\WawancaraDijadwalkan::class)
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-blue-100">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </span>
                            @else
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-primary/10">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </span>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            @if ($item->type === \App\Notifications\PenawaranDirespon::class)
                                @php
                                    $isAccepted = ($item->data['status'] ?? '') === 'accepted';
                                @endphp
                                <p class="text-sm font-semibold text-gray-900">
                                    Respon Penawaran: {{ $isAccepted ? 'Diterima' : 'Ditolak' }}
                                </p>
                                <p class="text-sm text-gray-600 mt-0.5">
                                    <span class="font-medium">{{ $item->data['nama_kandidat'] ?? '-' }}</span>
                                    {{ $isAccepted ? 'menerima' : 'menolak' }} penawaran untuk posisi
                                    <span class="font-medium">{{ $item->data['judul_posisi'] ?? '-' }}</span>.
                                </p>
                            @elseif ($item->type === \App\Notifications\WawancaraDijadwalkan::class)
                                <p class="text-sm font-semibold text-gray-900">
                                    Wawancara Dijadwalkan
                                </p>
                                <p class="text-sm text-gray-600 mt-0.5">
                                    Wawancara untuk <span class="font-medium">{{ $item->data['nama_kandidat'] ?? '-' }}</span>
                                    — posisi <span class="font-medium">{{ $item->data['judul_posisi'] ?? '-' }}</span>
                                    pada <span class="font-medium">{{ isset($item->data['jadwal_interview']) ? \Carbon\Carbon::parse($item->data['jadwal_interview'])->translatedFormat('d F Y, H:i') : '-' }}</span>.
                                </p>
                            @else
                                <p class="text-sm font-semibold text-gray-900">
                                    Pengingat: Lowongan Mendekati Tenggat
                                </p>
                                <p class="text-sm text-gray-600 mt-0.5">
                                    Lowongan <span class="font-medium">{{ $item->data['judul_posisi'] ?? '-' }}</span>
                                    memiliki kandidat ditangguhkan dan akan berakhir pada
                                    <span class="font-medium">{{ isset($item->data['tenggat_lamaran']) ? \Carbon\Carbon::parse($item->data['tenggat_lamaran'])->translatedFormat('d F Y') : '-' }}</span>.
                                </p>
                            @endif
                            <p class="text-xs text-gray-400 mt-1">{{ $item->created_at->diffForHumans() }}</p>
                        </div>
                        @if (! $item->read_at)
                            <span class="shrink-0 inline-block w-2 h-2 rounded-full bg-primary mt-1.5"></span>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $notifikasi->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
