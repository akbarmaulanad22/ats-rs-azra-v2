<x-layouts.public title="Tolak Penawaran - RS Azra" main-class="w-full bg-paper">

<div class="max-w-2xl mx-auto px-6 py-16">
    <div class="bg-white rounded-2xl border border-gray-200 p-8">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-2">Tolak Penawaran Kerja</h1>
            <p class="text-gray-600">Anda akan menolak penawaran kerja dari RS Azra. Tindakan ini tidak dapat dibatalkan.</p>
        </div>

        <div class="bg-gray-50 rounded-xl p-5 mb-6">
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

        <form method="POST" action="{{ request()->fullUrl() }}">
            @csrf

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Alasan Penolakan <span class="text-gray-400 font-normal">(opsional)</span>
                </label>
                <textarea
                    name="rejection_reason"
                    rows="4"
                    placeholder="Mohon beri tahu alasan Anda menolak penawaran ini..."
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-200 focus:border-red-300 resize-none placeholder:text-gray-400"
                >{{ old('rejection_reason') }}</textarea>
            </div>

            <div class="flex gap-3">
                <button
                    type="submit"
                    class="flex-1 px-5 py-2.5 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors cursor-pointer"
                >
                    Konfirmasi Penolakan
                </button>
            </div>
        </form>
    </div>
</div>

</x-layouts.public>
