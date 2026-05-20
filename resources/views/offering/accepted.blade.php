<x-layouts.public title="Penawaran Diterima - RS Azra" main-class="w-full bg-paper">

<div class="max-w-2xl mx-auto px-6 py-16">
    <div class="bg-white rounded-2xl border border-gray-200 p-8 text-center">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-900 mb-2">Penawaran Diterima</h1>
        <p class="text-gray-600 mb-8">Terima kasih telah menerima penawaran kerja dari RS Azra. Tim HR kami akan segera menghubungi Anda untuk langkah selanjutnya.</p>

        <div class="bg-gray-50 rounded-xl p-5 text-left">
            <h2 class="text-sm font-semibold text-gray-800 mb-3">Ringkasan Penawaran</h2>
            <dl class="space-y-2">
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Posisi</dt>
                    <dd class="text-sm font-medium text-gray-900">{{ $offering->jabatan_ditawarkan }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Gaji</dt>
                    <dd class="text-sm font-medium text-gray-900">{{ $offering->gaji }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Tanggal Mulai</dt>
                    <dd class="text-sm font-medium text-gray-900">{{ $offering->tanggal_mulai->format('d M Y') }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>

</x-layouts.public>
