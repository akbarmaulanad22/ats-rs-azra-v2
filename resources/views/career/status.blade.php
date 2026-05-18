<x-layouts.public title="Status Lamaran - RS Azra" main-class="w-full bg-paper">

<style>
    /* ── Status hero ────────────────────────────────────── */
    .status-hero {
        background: #ffffff;
        border-bottom: 1px solid #d9ddd9;
    }
    .status-hero-inner {
        max-width: 900px; margin: 0 auto;
        padding: 48px 28px 36px;
    }
    .status-eyebrow {
        font-family: "IBM Plex Mono", monospace;
        font-size: 11px; color: #005f5c;
        text-transform: uppercase; letter-spacing: 0.14em; font-weight: 500;
        margin-bottom: 16px;
        display: flex; align-items: center; gap: 10px;
    }
    .status-eyebrow::before {
        content: ""; width: 28px; height: 1px; background: rgb(0,119,116);
    }
    .status-h1 {
        font-family: "IBM Plex Serif", Georgia, serif;
        font-weight: 500;
        font-size: clamp(28px, 4vw, 40px);
        line-height: 1.1; letter-spacing: -0.02em;
        margin: 0 0 10px; color: #0d1614;
    }
    .status-lede {
        font-size: 15px; line-height: 1.55; color: #2a3835;
        max-width: 56ch; margin: 0;
    }

    /* ── Content area ───────────────────────────────────── */
    .status-content {
        max-width: 900px; margin: 0 auto;
        padding: 32px 28px 60px;
    }
    .status-section {
        border-top: 2px solid #0d1614;
        padding-top: 16px;
        margin-bottom: 36px;
    }
    .status-section-h2 {
        font-family: "IBM Plex Mono", monospace;
        font-size: 11px; text-transform: uppercase;
        letter-spacing: 0.1em; margin: 0 0 18px;
        color: #0d1614; font-weight: 600;
    }

    /* ── Info DL ─────────────────────────────────────────── */
    .status-dl { margin: 0; }
    .status-dl-row {
        display: flex; gap: 16px;
        padding: 10px 0;
        border-bottom: 1px solid #ebeeea;
    }
    .status-dl-row:last-child { border-bottom: 0; }
    .status-dl-label {
        font-family: "IBM Plex Mono", monospace;
        font-size: 11px; color: #5a6864;
        text-transform: uppercase; letter-spacing: 0.06em;
        width: 120px; flex-shrink: 0; padding-top: 2px;
    }
    .status-dl-value {
        font-size: 15px; color: #0d1614; font-weight: 500;
        min-width: 0;
    }

    /* ── Stage timeline ─────────────────────────────────── */
    .status-stages { list-style: none; margin: 0; padding: 0; }
    .status-stage {
        display: flex; gap: 16px;
        padding: 0;
    }
    .status-stage-rail {
        display: flex; flex-direction: column; align-items: center;
        flex-shrink: 0; width: 28px;
    }
    .status-stage-dot {
        width: 28px; height: 28px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .status-stage-dot.done { background: #5e9425; }
    .status-stage-dot.active { background: rgb(0,119,116); }
    .status-stage-dot.reserved { background: #c08a2c; }
    .status-stage-dot.gagal { background: #b54327; }
    .status-stage-dot.pending { background: #d9ddd9; }
    .status-stage-dot svg { width: 14px; height: 14px; color: white; }
    .status-stage-dot .dot-inner { width: 8px; height: 8px; border-radius: 50%; background: white; }
    .status-stage-dot .dot-num {
        font-family: "IBM Plex Mono", monospace;
        font-size: 10px; color: #8a948f; font-weight: 500;
    }
    .status-stage-line {
        width: 1px; flex: 1; margin: 4px 0;
        background: #ebeeea;
    }
    .status-stage-line.done { background: #c3db9e; }
    .status-stage-body {
        padding: 4px 0 24px;
        min-width: 0;
    }
    .status-stage:last-child .status-stage-body { padding-bottom: 0; }
    .status-stage-name {
        font-family: "IBM Plex Sans", system-ui, sans-serif;
        font-size: 15px; font-weight: 500; color: #0d1614;
        margin: 0 0 2px; line-height: 1.3;
    }
    .status-stage-name.done-text { color: #2a3835; }
    .status-stage-name.active-text { color: #0d1614; font-weight: 600; }
    .status-stage-name.reserved-text { color: #7a5a1a; }
    .status-stage-name.gagal-text { color: #7a2d1a; }
    .status-stage-name.pending-text { color: #8a948f; font-weight: 400; }
    .status-stage-desc {
        font-family: "IBM Plex Mono", monospace;
        font-size: 11px; letter-spacing: 0.04em; margin: 0;
    }
    .status-stage-desc.done-desc { color: #5e9425; }
    .status-stage-desc.active-desc { color: rgb(0,119,116); }
    .status-stage-desc.reserved-desc { color: #a07430; }
    .status-stage-desc.gagal-desc { color: #b54327; }
    .status-stage-desc.pending-desc { color: #b8c0bd; }
    .status-stage-dimmed { opacity: 0.4; }

    /* ── Footer actions ─────────────────────────────────── */
    .status-actions {
        max-width: 900px; margin: 0 auto;
        padding: 0 28px 48px;
        border-top: 1px solid #d9ddd9;
        padding-top: 20px;
    }
    .status-link {
        font-family: "IBM Plex Sans", system-ui, sans-serif;
        font-size: 13px; font-weight: 600; color: rgb(0,119,116);
        text-decoration: none;
        display: inline-flex; align-items: center; gap: 6px;
        transition: color 0.15s;
    }
    .status-link:hover { color: rgb(0,88,85); }

    /* ── Responsive ──────────────────────────────────────── */
    @media (max-width: 1100px) {
        .status-hero-inner { padding: 36px 20px 28px; }
        .status-h1 { font-size: clamp(24px, 5vw, 32px); }
        .status-content { padding: 24px 16px 48px; }
        .status-actions { padding: 0 16px 40px; padding-top: 18px; }
    }
    @media (max-width: 520px) {
        .status-dl-row { flex-direction: column; gap: 2px; }
        .status-dl-label { width: auto; }
    }
</style>

{{-- ─── HERO ────────────────────────────────────────────────── --}}
<section class="status-hero">
    <div class="status-hero-inner">
        <div class="status-eyebrow">Status Lamaran</div>
        <h1 class="status-h1">{{ $application->vacancy->judul_posisi }}</h1>
        <p class="status-lede">{{ $application->vacancy->unit->nama }}</p>
    </div>
</section>

{{-- ─── CONTENT ─────────────────────────────────────────────── --}}
<div class="status-content">

    {{-- ── Candidate info ─────────────────────────────────── --}}
    <div class="status-section">
        <h2 class="status-section-h2">Informasi Pelamar</h2>
        <dl class="status-dl">
            <div class="status-dl-row">
                <dt class="status-dl-label">Nama</dt>
                <dd class="status-dl-value">{{ $application->candidate->nama_lengkap }}</dd>
            </div>
            <div class="status-dl-row">
                <dt class="status-dl-label">Posisi</dt>
                <dd class="status-dl-value">{{ $application->vacancy->judul_posisi }}</dd>
            </div>
            <div class="status-dl-row">
                <dt class="status-dl-label">Unit</dt>
                <dd class="status-dl-value">{{ $application->vacancy->unit->nama }}</dd>
            </div>
        </dl>
    </div>

    {{-- ── Stage timeline ─────────────────────────────────── --}}
    <div class="status-section">
        <h2 class="status-section-h2">Tahapan Seleksi</h2>
        @php
            $gagalStage = $application->stages->firstWhere('status', \App\Enums\ApplicationStageStatus::Gagal);
            $gagalPosition = $gagalStage?->position;
        @endphp
        <ol class="status-stages">
            @foreach ($application->stages as $stage)
                @php
                    $isAfterGagal = $gagalPosition !== null && $stage->position > $gagalPosition;
                    $isLast = $loop->last;
                @endphp
                <li class="status-stage {{ $isAfterGagal ? 'status-stage-dimmed' : '' }}">
                    {{-- Rail: dot + line --}}
                    <div class="status-stage-rail">
                        @if ($stage->status === \App\Enums\ApplicationStageStatus::Selesai)
                            <div class="status-stage-dot done">
                                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        @elseif ($stage->status === \App\Enums\ApplicationStageStatus::Aktif)
                            <div class="status-stage-dot active">
                                <div class="dot-inner"></div>
                            </div>
                        @elseif ($stage->status === \App\Enums\ApplicationStageStatus::Reserved)
                            <div class="status-stage-dot reserved">
                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        @elseif ($stage->status === \App\Enums\ApplicationStageStatus::Gagal)
                            <div class="status-stage-dot gagal">
                                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </div>
                        @else
                            <div class="status-stage-dot pending">
                                <span class="dot-num">{{ $loop->iteration }}</span>
                            </div>
                        @endif
                        @unless ($isLast)
                            <div class="status-stage-line {{ $stage->status === \App\Enums\ApplicationStageStatus::Selesai ? 'done' : '' }}"></div>
                        @endunless
                    </div>

                    {{-- Stage info --}}
                    <div class="status-stage-body">
                        <p class="status-stage-name
                            @if ($stage->status === \App\Enums\ApplicationStageStatus::Gagal) gagal-text
                            @elseif ($stage->status === \App\Enums\ApplicationStageStatus::Aktif) active-text
                            @elseif ($stage->status === \App\Enums\ApplicationStageStatus::Reserved) reserved-text
                            @elseif ($stage->status === \App\Enums\ApplicationStageStatus::Selesai) done-text
                            @else pending-text
                            @endif">
                            {{ $stage->nama }}
                        </p>
                        <p class="status-stage-desc
                            @if ($stage->status === \App\Enums\ApplicationStageStatus::Gagal) gagal-desc
                            @elseif ($stage->status === \App\Enums\ApplicationStageStatus::Selesai) done-desc
                            @elseif ($stage->status === \App\Enums\ApplicationStageStatus::Aktif) active-desc
                            @elseif ($stage->status === \App\Enums\ApplicationStageStatus::Reserved) reserved-desc
                            @else pending-desc
                            @endif">
                            @if ($stage->status === \App\Enums\ApplicationStageStatus::Selesai)
                                Selesai &middot; {{ $stage->updated_at->format('d/m/Y') }}
                            @elseif ($stage->status === \App\Enums\ApplicationStageStatus::Aktif)
                                Sedang berlangsung
                            @elseif ($stage->status === \App\Enums\ApplicationStageStatus::Reserved)
                                Ditangguhkan
                            @elseif ($stage->status === \App\Enums\ApplicationStageStatus::Gagal)
                                Tidak Lolos
                            @else
                                Menunggu
                            @endif
                        </p>
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</div>

{{-- ─── ACTIONS ─────────────────────────────────────────────── --}}
<div class="status-actions">
    <a href="{{ route('karier.index') }}" class="status-link">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
        Lihat lowongan lainnya
    </a>
</div>

</x-layouts.public>
