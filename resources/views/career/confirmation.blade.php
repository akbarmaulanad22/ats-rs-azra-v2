<x-layouts.public title="Lamaran Terkirim - RS Azra" main-class="w-full bg-paper">

<style>
    /* ── Confirmation hero ──────────────────────────────── */
    .conf-hero {
        background: #ffffff;
        border-bottom: 1px solid #d9ddd9;
    }
    .conf-hero-inner {
        max-width: 900px; margin: 0 auto;
        padding: 48px 28px 36px;
    }
    .conf-eyebrow {
        font-family: "IBM Plex Mono", monospace;
        font-size: 11px; color: #005f5c;
        text-transform: uppercase; letter-spacing: 0.14em; font-weight: 500;
        margin-bottom: 16px;
        display: flex; align-items: center; gap: 10px;
    }
    .conf-eyebrow::before {
        content: ""; width: 28px; height: 1px; background: rgb(0,119,116);
    }
    .conf-h1 {
        font-family: "IBM Plex Serif", Georgia, serif;
        font-weight: 500;
        font-size: clamp(28px, 4vw, 40px);
        line-height: 1.1; letter-spacing: -0.02em;
        margin: 0 0 10px; color: #0d1614;
    }
    .conf-lede {
        font-size: 15px; line-height: 1.55; color: #2a3835;
        max-width: 56ch; margin: 0;
    }

    /* ── Success banner ─────────────────────────────────── */
    .conf-success {
        max-width: 900px; margin: 0 auto;
        padding: 0 28px;
        transform: translateY(-24px);
    }
    .conf-success-card {
        background: #f0f7e6;
        border: 1px solid #c3db9e;
        padding: 16px 20px;
        display: flex; align-items: flex-start; gap: 12px;
    }
    .conf-success-card svg { flex-shrink: 0; color: #5e9425; margin-top: 2px; }
    .conf-success-title {
        font-family: "IBM Plex Sans", system-ui, sans-serif;
        font-size: 14px; font-weight: 600; color: #3a5c14; margin: 0 0 2px;
    }
    .conf-success-desc {
        font-size: 12.5px; color: #5e7a35; margin: 0; line-height: 1.5;
    }

    /* ── Content area ───────────────────────────────────── */
    .conf-content {
        max-width: 900px; margin: 0 auto;
        padding: 0 28px 60px;
    }
    .conf-section {
        border-top: 2px solid #0d1614;
        padding-top: 16px;
        margin-bottom: 36px;
    }
    .conf-section-h2 {
        font-family: "IBM Plex Mono", monospace;
        font-size: 11px; text-transform: uppercase;
        letter-spacing: 0.1em; margin: 0 0 18px;
        color: #0d1614; font-weight: 600;
    }

    /* ── Summary DL ─────────────────────────────────────── */
    .conf-dl { margin: 0; }
    .conf-dl-row {
        display: flex; gap: 16px;
        padding: 10px 0;
        border-bottom: 1px solid #ebeeea;
    }
    .conf-dl-row:last-child { border-bottom: 0; }
    .conf-dl-label {
        font-family: "IBM Plex Mono", monospace;
        font-size: 11px; color: #5a6864;
        text-transform: uppercase; letter-spacing: 0.06em;
        width: 120px; flex-shrink: 0; padding-top: 2px;
    }
    .conf-dl-value {
        font-size: 15px; color: #0d1614; font-weight: 500;
        min-width: 0;
    }
    .conf-dl-value.mono {
        font-family: "IBM Plex Mono", monospace;
        font-size: 13px; font-weight: 400;
        word-break: break-all;
    }

    /* ── Stage timeline ─────────────────────────────────── */
    .conf-stages { list-style: none; margin: 0; padding: 0; }
    .conf-stage {
        display: flex; align-items: center; gap: 14px;
        padding: 10px 0;
        border-bottom: 1px solid #ebeeea;
    }
    .conf-stage:last-child { border-bottom: 0; }
    .conf-stage-dot {
        width: 28px; height: 28px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .conf-stage-dot.done { background: #5e9425; }
    .conf-stage-dot.active { background: rgb(0,119,116); }
    .conf-stage-dot.pending { background: #d9ddd9; }
    .conf-stage-dot svg { width: 14px; height: 14px; color: white; }
    .conf-stage-dot .dot-num {
        font-family: "IBM Plex Mono", monospace;
        font-size: 10px; color: #8a948f; font-weight: 500;
    }
    .conf-stage-name {
        font-size: 14px; color: #5a6864;
    }
    .conf-stage-name.active {
        font-weight: 600; color: #0d1614;
    }

    /* ── Footer actions ─────────────────────────────────── */
    .conf-actions {
        max-width: 900px; margin: 0 auto;
        padding: 0 28px 48px;
        display: flex; align-items: center; justify-content: space-between;
        gap: 16px; flex-wrap: wrap;
        border-top: 1px solid #d9ddd9;
        padding-top: 20px;
    }
    .conf-link {
        font-family: "IBM Plex Sans", system-ui, sans-serif;
        font-size: 13px; font-weight: 600; color: rgb(0,119,116);
        text-decoration: none;
        display: inline-flex; align-items: center; gap: 6px;
        transition: color 0.15s;
    }
    .conf-link:hover { color: rgb(0,88,85); }
    .conf-link-primary {
        padding: 10px 20px;
        background: rgb(0,119,116); color: white;
        font-size: 13px; font-weight: 600;
        text-decoration: none;
        display: inline-flex; align-items: center; gap: 8px;
        transition: background 0.15s;
        font-family: "IBM Plex Sans", system-ui, sans-serif;
    }
    .conf-link-primary:hover { background: rgb(0,88,85); }

    /* ── Responsive ──────────────────────────────────────── */
    @media (max-width: 1100px) {
        .conf-hero-inner { padding: 36px 20px 28px; }
        .conf-h1 { font-size: clamp(24px, 5vw, 32px); }
        .conf-success { padding: 0 16px; }
        .conf-content { padding: 0 16px 48px; }
        .conf-actions { padding: 0 16px 40px; padding-top: 18px; }
    }
    @media (max-width: 520px) {
        .conf-dl-row { flex-direction: column; gap: 2px; }
        .conf-dl-label { width: auto; }
        .conf-actions { flex-direction: column; align-items: stretch; text-align: center; }
    }
</style>

{{-- ─── HERO ────────────────────────────────────────────────── --}}
<section class="conf-hero">
    <div class="conf-hero-inner">
        <div class="conf-eyebrow">Konfirmasi Lamaran</div>
        <h1 class="conf-h1">Lamaran Berhasil Dikirim</h1>
        <p class="conf-lede">{{ $application->vacancy->judul_posisi }} &mdash; {{ $application->vacancy->unit->nama }}</p>
    </div>
</section>

{{-- ─── SUCCESS BANNER ──────────────────────────────────────── --}}
<div class="conf-success">
    <div class="conf-success-card">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <p class="conf-success-title">Lamaran Anda telah kami terima.</p>
            <p class="conf-success-desc">Simpan halaman ini atau catat kode lamaran Anda untuk memantau status.</p>
        </div>
    </div>
</div>

{{-- ─── APPLICATION SUMMARY ─────────────────────────────────── --}}
<div class="conf-content">
    <div class="conf-section">
        <h2 class="conf-section-h2">Ringkasan Lamaran</h2>
        <dl class="conf-dl">
            <div class="conf-dl-row">
                <dt class="conf-dl-label">Nama</dt>
                <dd class="conf-dl-value">{{ $application->candidate->nama_lengkap }}</dd>
            </div>
            <div class="conf-dl-row">
                <dt class="conf-dl-label">Email</dt>
                <dd class="conf-dl-value">{{ $application->candidate->email }}</dd>
            </div>
            <div class="conf-dl-row">
                <dt class="conf-dl-label">Posisi</dt>
                <dd class="conf-dl-value">{{ $application->vacancy->judul_posisi }}</dd>
            </div>
            <div class="conf-dl-row">
                <dt class="conf-dl-label">Kode Lamaran</dt>
                <dd class="conf-dl-value mono">{{ $application->token }}</dd>
            </div>
        </dl>
    </div>

    {{-- ─── STAGES ──────────────────────────────────────────── --}}
    <div class="conf-section">
        <h2 class="conf-section-h2">Tahapan Seleksi</h2>
        <ol class="conf-stages">
            @foreach ($application->stages as $stage)
                <li class="conf-stage">
                    @if ($stage->status === \App\Enums\ApplicationStageStatus::Selesai)
                        <div class="conf-stage-dot done">
                            <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    @elseif ($stage->status === \App\Enums\ApplicationStageStatus::Aktif)
                        <div class="conf-stage-dot active">
                            <div style="width:8px; height:8px; border-radius:50%; background:white;"></div>
                        </div>
                    @else
                        <div class="conf-stage-dot pending">
                            <span class="dot-num">{{ $loop->iteration }}</span>
                        </div>
                    @endif
                    <span class="conf-stage-name {{ $stage->status === \App\Enums\ApplicationStageStatus::Aktif ? 'active' : '' }}">
                        {{ $stage->nama }}
                    </span>
                </li>
            @endforeach
        </ol>
    </div>
</div>

{{-- ─── ACTIONS ─────────────────────────────────────────────── --}}
<div class="conf-actions">
    <a href="{{ route('karier.index') }}" class="conf-link">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
        Lihat lowongan lainnya
    </a>
    <a href="{{ route('karier.lamaran.status', $application->token) }}" class="conf-link-primary">
        Cek status lamaran
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>
    </a>
</div>

</x-layouts.public>
