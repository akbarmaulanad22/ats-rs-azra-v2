{{-- Aksi Tahap: Lamaran (auto-advanced on submission) --}}
<div class="bg-white rounded-xl border border-gray-100 p-5">
    <h2 class="text-sm font-semibold text-gray-800 mb-3">Tahap Lamaran</h2>
    <div class="bg-green-50 border border-green-200 rounded-lg p-3">
        <p class="text-xs text-green-700">Kandidat telah menyelesaikan pengisian formulir lamaran. Tahap ini otomatis dilanjutkan setelah lamaran dikirim.</p>
    </div>
    <dl class="mt-4 space-y-2">
        <div>
            <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">Tanggal Melamar</dt>
            <dd class="text-xs text-gray-800 mt-0.5">{{ $application->created_at->translatedFormat('d F Y, H:i') }}</dd>
        </div>
    </dl>
</div>
