<x-layouts.public title="{{ $vacancy->judul_posisi }} - RS Azra" main-class="w-full bg-paper">

<style>
    .detail-wrap {
        max-width: 1320px; margin: 0 auto;
        padding: 56px 28px 80px;
    }
    .detail-back {
        font-family: "IBM Plex Mono", monospace;
        font-size: 11px; text-transform: uppercase;
        letter-spacing: 0.1em; color: #5a6864;
        text-decoration: none;
        display: inline-flex; align-items: center; gap: 8px;
        margin-bottom: 40px;
        transition: color 0.15s;
    }
    .detail-back:hover { color: rgb(0,119,116); }
    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 48px;
        align-items: start;
    }
    /* Left column */
    .detail-eyebrow {
        font-family: "IBM Plex Mono", monospace;
        font-size: 11px; color: #005f5c;
        text-transform: uppercase; letter-spacing: 0.14em; font-weight: 500;
        margin-bottom: 16px;
        display: flex; align-items: center; gap: 10px;
    }
    .detail-eyebrow::before {
        content: ""; width: 28px; height: 1px; background: rgb(0,119,116);
    }
    .detail-title {
        font-family: "IBM Plex Serif", Georgia, serif;
        font-weight: 500;
        font-size: clamp(28px, 4vw, 48px);
        line-height: 1.08; letter-spacing: -0.02em;
        margin: 0 0 24px; color: #0d1614;
    }
    .detail-tags { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 32px; }
    .detail-tag {
        font-family: "IBM Plex Mono", monospace;
        font-size: 10.5px; text-transform: uppercase;
        letter-spacing: 0.06em; padding: 4px 10px;
        background: #efede5; color: #2a3835; border-radius: 2px;
    }
    .detail-tag.dept { background: #e5f1f0; color: #005f5c; }
    .detail-section { margin-bottom: 36px; }
    .detail-section-h {
        font-family: "IBM Plex Mono", monospace;
        font-size: 11px; text-transform: uppercase;
        letter-spacing: 0.1em; font-weight: 600;
        color: #0d1614; margin: 0 0 12px;
        border-top: 2px solid #0d1614;
        padding-top: 14px;
    }
    .detail-body {
        font-size: 15px; line-height: 1.65; color: #2a3835;
        white-space: pre-line;
    }

    /* Right sidebar */
    .detail-sidebar {
        position: sticky; top: 100px;
    }
    .sidebar-card {
        border: 1px solid #0d1614;
        background: #fff;
        overflow: hidden;
    }
    .sidebar-card-h {
        padding: 16px 20px;
        font-family: "IBM Plex Mono", monospace;
        font-size: 11px; text-transform: uppercase;
        letter-spacing: 0.1em; font-weight: 600;
    }
    .sidebar-meta { padding: 20px; display: flex; flex-direction: column; gap: 16px; }
    .sidebar-row {}
    .sidebar-row-label {
        font-family: "IBM Plex Mono", monospace;
        font-size: 10px; text-transform: uppercase;
        letter-spacing: 0.08em; color: #8a948f;
        margin-bottom: 4px;
    }
    .sidebar-row-val {
        font-size: 14px; font-weight: 500; color: #0d1614;
    }
    .apply-cta {
        display: block; text-align: center;
        /*background: rgb(0,119,116); color: white;*/
        padding: 14px 20px;
        font-size: 14px; font-weight: 600;
        text-decoration: none;
        font-family: "IBM Plex Sans", system-ui, sans-serif;
        display: flex; align-items: center; justify-content: center; gap: 10px;
        transition: background 0.15s;
        margin: 0 20px 20px;
    }
    /*.apply-cta:hover { background: rgb(0,88,85); }*/

    @media (max-width: 900px) {
        .detail-wrap { padding: 32px 16px 60px; }
        .detail-grid { grid-template-columns: 1fr; gap: 32px; }
        .detail-sidebar { position: static; }
    }
</style>

<div class="detail-wrap">
    <a href="{{ route('karier.index') }}" class="detail-back">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="M11 6l-6 6 6 6"/></svg>
        Kembali ke Lowongan
    </a>

    <div class="detail-grid">
        {{-- Left: job detail --}}
        <div>
            <div class="detail-eyebrow">{{ $vacancy->unit->nama }}</div>
            <h1 class="detail-title">{{ $vacancy->judul_posisi }}</h1>
            <div class="detail-tags">
                <span class="detail-tag dept">{{ $vacancy->unit->nama }}</span>
                <span class="detail-tag">{{ $vacancy->jenis_pekerjaan->label() }}</span>
                @if ($vacancy->created_at->gte(now()->subDays(3)))
                    <span class="detail-tag" style="background:#f0f7e6;color:#5e9425;">Baru</span>
                @endif
                @if ($vacancy->tenggat_lamaran->lte(now()->addDays(7)))
                    <span class="detail-tag" style="background:#f8e6e1;color:#b54327;">Mendesak</span>
                @endif
            </div>

            <div class="detail-section">
                <h2 class="detail-section-h">Deskripsi Pekerjaan</h2>
                <div class="detail-body">{{ $vacancy->deskripsi_pekerjaan }}</div>
            </div>

            <div class="detail-section">
                <h2 class="detail-section-h">Kualifikasi</h2>
                <div class="detail-body">{{ $vacancy->kualifikasi }}</div>
            </div>
        </div>

        {{-- Right: sidebar --}}
        <aside class="detail-sidebar">
            <div class="sidebar-card">
                <div class="sidebar-card-h bg-primary text-white">Detail Posisi</div>
                <div class="sidebar-meta">
                    <div class="sidebar-row">
                        <div class="sidebar-row-label">Jenis Pekerjaan</div>
                        <div class="sidebar-row-val">{{ $vacancy->jenis_pekerjaan->label() }}</div>
                    </div>
                    <div class="sidebar-row">
                        <div class="sidebar-row-label">Jumlah Posisi</div>
                        <div class="sidebar-row-val">{{ $vacancy->jumlah_posisi }}</div>
                    </div>
                    <div class="sidebar-row">
                        <div class="sidebar-row-label">Tenggat Lamaran</div>
                        <div class="sidebar-row-val">{{ $vacancy->tenggat_lamaran->format('d M Y') }}</div>
                    </div>
                    <div class="sidebar-row">
                        <div class="sidebar-row-label">Ditayangkan</div>
                        <div class="sidebar-row-val">{{ $vacancy->created_at->locale('id')->diffForHumans() }}</div>
                    </div>
                </div>
                <a href="{{ route('karier.lamar', $vacancy) }}" class="apply-cta bg-secondary text-white hover:bg-secondary-dark">
                    Lamar Sekarang
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>
                </a>
            </div>
        </aside>
    </div>
</div>

</x-layouts.public>
