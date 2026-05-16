<x-layouts.public title="Lowongan Kerja - RS Azra" main-class="w-full bg-paper">

<style>
    /* ── Hero ────────────────────────────────────────────── */
    .career-hero {
        background: #ffffff;
        border-bottom: 1px solid #d9ddd9;
        position: relative;
    }
    .hero-inner {
        max-width: 1320px; margin: 0 auto;
        padding: 56px 28px 40px;
        display: grid;
        grid-template-columns: 1.4fr 1fr;
        gap: 64px;
        align-items: end;
        position: relative; z-index: 2;
    }
    .hero-eyebrow {
        font-family: "IBM Plex Mono", monospace;
        font-size: 11px; color: #005f5c;
        text-transform: uppercase; letter-spacing: 0.14em; font-weight: 500;
        margin-bottom: 20px;
        display: flex; align-items: center; gap: 10px;
    }
    .hero-eyebrow::before {
        content: ""; width: 28px; height: 1px; background: rgb(0,119,116);
    }
    .career-h1 {
        font-family: "IBM Plex Serif", Georgia, serif;
        font-weight: 500;
        font-size: clamp(36px, 5.2vw, 64px);
        line-height: 1.02; letter-spacing: -0.025em;
        margin: 0 0 22px; color: #0d1614;
        text-wrap: balance;
    }
    .career-h1 em { font-style: italic; color: rgb(0,119,116); font-weight: 500; }
    .career-h1 .accent {
        background: linear-gradient(transparent 62%, rgba(129,189,65,0.25) 62%);
        padding: 0 4px;
    }
    .hero-lede {
        font-size: 16px; line-height: 1.55; color: #2a3835;
        max-width: 56ch; margin: 0 0 36px;
    }
    .hero-meta {
        display: flex; gap: 32px;
        border-top: 1px solid #d9ddd9;
        padding-top: 18px; margin-top: 32px;
    }
    .hero-stat {
        font-family: "IBM Plex Mono", monospace;
        font-size: 11px; color: #5a6864;
        text-transform: uppercase; letter-spacing: 0.06em;
    }
    .hero-stat strong {
        display: block;
        font-family: "IBM Plex Serif", serif;
        font-size: 26px; font-weight: 500; color: #0d1614;
        text-transform: none; letter-spacing: -0.01em; margin-bottom: 2px;
    }
    .hero-illo { position: relative; height: 360px; overflow: hidden; }
    .hero-illo svg { width: 100%; height: 100%; }

    /* ── Search panel ────────────────────────────────────── */
    .search-panel {
        max-width: 1320px; margin: 0 auto;
        padding: 0 28px;
        position: relative; z-index: 3;
        transform: translateY(50%);
    }
    .search-card {
        background: #ffffff;
        border: 1px solid #0d1614;
        box-shadow: 0 24px 48px -24px rgba(13,22,20,0.18);
        display: grid;
        grid-template-columns: 1fr auto;
        align-items: stretch;
    }
    .search-field {
        padding: 18px 22px;
        border-right: 1px solid #d9ddd9;
        display: flex; flex-direction: column; gap: 4px;
    }
    .search-field label {
        font-family: "IBM Plex Mono", monospace;
        font-size: 10px; text-transform: uppercase;
        letter-spacing: 0.1em; color: #5a6864; font-weight: 500;
    }
    .search-field input {
        border: 0; padding: 4px 0; outline: none;
        background: transparent;
        font-size: 15px; font-weight: 500; color: #0d1614; width: 100%;
        font-family: "IBM Plex Sans", system-ui, sans-serif;
    }
    .search-field input::placeholder { color: #8a948f; font-weight: 400; }
    .search-submit {
        background: rgb(0,119,116); color: white;
        border: 0; padding: 0 30px;
        font-size: 14px; font-weight: 600;
        display: flex; align-items: center; gap: 10px;
        cursor: pointer; transition: background 0.15s;
        font-family: "IBM Plex Sans", system-ui, sans-serif;
        white-space: nowrap;
    }
    .search-submit:hover { background: rgb(0,88,85); }

    /* ── Listings ────────────────────────────────────────── */
    .listings-wrap {
        max-width: 1320px; margin: 0 auto;
        padding: 100px 28px 80px;
        display: grid;
        grid-template-columns: 260px 1fr;
        gap: 40px;
        align-items: start;
    }
    .career-sidebar {
        position: sticky; top: 100px;
        border-top: 2px solid #0d1614;
        padding-top: 16px;
    }
    .filter-h3 {
        font-family: "IBM Plex Mono", monospace;
        font-size: 11px; text-transform: uppercase;
        letter-spacing: 0.1em; margin: 0 0 14px;
        color: #0d1614; font-weight: 600;
    }
    .filter-group { border-bottom: 1px solid #d9ddd9; padding-bottom: 18px; margin-bottom: 18px; }
    .filter-group-h4 {
        font-size: 12px; font-weight: 600; margin: 0 0 10px; color: #0d1614;
        display: flex; justify-content: space-between;
    }
    .filter-chk {
        display: flex; align-items: center; gap: 10px;
        padding: 4px 0; font-size: 13px; color: #2a3835;
        cursor: pointer; user-select: none;
    }
    .filter-chk input { accent-color: rgb(0,119,116); margin: 0; }
    .filter-chk .count {
        margin-left: auto;
        font-family: "IBM Plex Mono", monospace;
        font-size: 11px; color: #8a948f;
    }
    .filter-chk:hover { color: #0d1614; }
    .filter-chk:hover .count { color: #5a6864; }

    /* ── Results ─────────────────────────────────────────── */
    .results-head {
        display: flex; align-items: baseline; justify-content: space-between;
        border-top: 2px solid #0d1614;
        border-bottom: 1px solid #d9ddd9;
        padding-top: 16px; padding-bottom: 14px;
    }
    .results-count {
        font-family: "IBM Plex Serif", serif;
        font-size: 24px; font-weight: 500; letter-spacing: -0.01em;
    }
    .results-count em { font-style: normal; color: #005f5c; }

    /* ── Job rows ────────────────────────────────────────── */
    .jobs { display: flex; flex-direction: column; }
    .job {
        display: grid;
        grid-template-columns: 80px 1fr auto;
        gap: 24px;
        padding: 16px 20px;
        padding-top: 22px;
        margin: 0 -20px;
        border-bottom: 1px solid #ebeeea;
        align-items: start;
        position: relative;
        transition: background 0.15s;
    }
    .job:nth-child(odd) { background: #efede5; }
    .job:hover { background: linear-gradient(to right, #e5f1f0 0%, transparent 60%) !important; }
    .job-num {
        font-family: "IBM Plex Mono", monospace;
        font-size: 11px; color: #8a948f; letter-spacing: 0.04em;
    }
    .job-num strong {
        display: block;
        font-family: "IBM Plex Serif", serif;
        font-size: 22px; color: #0d1614; font-weight: 500;
        margin-bottom: 4px; letter-spacing: -0.01em;
    }
    .job-body { min-width: 0; }
    .job-title {
        font-family: "IBM Plex Serif", serif;
        font-size: 21px; font-weight: 500;
        letter-spacing: -0.015em; margin: 0 0 6px; line-height: 1.2;
    }
    .job-title a {
        text-decoration: none; color: #0d1614;
        background-image: linear-gradient(rgb(0,119,116), rgb(0,119,116));
        background-size: 0 1px; background-repeat: no-repeat;
        background-position: 0 100%; transition: background-size 0.25s;
    }
    .job:hover .job-title a { background-size: 100% 1px; color: #005f5c; }
    .job-tags { display: flex; gap: 6px; flex-wrap: wrap; margin: 0 0 10px; }
    .job-tag {
        font-family: "IBM Plex Mono", monospace;
        font-size: 10.5px; text-transform: uppercase;
        letter-spacing: 0.06em; padding: 3px 8px;
        background: #efede5; color: #2a3835; border-radius: 2px;
    }
    .job-tag.dept { background: #e5f1f0; color: #005f5c; }
    .job-tag.urgent { background: #f8e6e1; color: #b54327; }
    .job-tag.new { background: #f0f7e6; color: #5e9425; }
    .job-meta {
        display: flex; gap: 18px; flex-wrap: wrap;
        font-size: 12.5px; color: #5a6864;
    }
    .job-aside {
        text-align: right; display: flex;
        flex-direction: column; align-items: flex-end; gap: 10px;
    }
    .apply-btn {
        padding: 8px 16px;
        background: rgb(0,119,116); color: white;
        font-size: 12px; font-weight: 600;
        border-radius: 2px; text-decoration: none;
        display: inline-flex; align-items: center; gap: 8px;
        transition: background 0.15s; white-space: nowrap;
        font-family: "IBM Plex Sans", system-ui, sans-serif;
    }
    .apply-btn:hover { background: rgb(0,88,85); }

    /* ── Pagination ──────────────────────────────────────── */
    .pagination-wrap {
        display: flex; align-items: center;
        justify-content: space-between;
        gap: 16px; padding: 28px 0 0; flex-wrap: wrap;
    }
    .pagination-info {
        font-family: "IBM Plex Mono", monospace;
        font-size: 11px; color: #5a6864;
        text-transform: uppercase; letter-spacing: 0.08em;
    }
    .pagination-info strong {
        color: #0d1614;
        font-family: "IBM Plex Serif", serif;
        font-size: 14px; font-weight: 500;
        letter-spacing: 0; text-transform: none; margin: 0 2px;
    }
    .pagination-wrap nav a[rel="next"],
    .pagination-wrap nav span[aria-disabled="true"] > span { padding-right: 12px; }

    /* ── Responsive ──────────────────────────────────────── */
    @media (max-width: 1100px) {
        .hero-inner { grid-template-columns: 1fr; gap: 24px; padding: 36px 20px 32px; }
        .career-h1 { font-size: clamp(32px, 7vw, 44px); }
        .hero-illo { height: 200px; order: -1; }
        .hero-meta { gap: 18px; }
        .hero-stat strong { font-size: 20px; }
        .search-panel { padding: 0 16px; }
        .search-card { grid-template-columns: 1fr; }
        .search-field { border-right: 0; border-bottom: 1px solid #d9ddd9; padding: 14px 18px; }
        .search-submit { padding: 16px; justify-content: center; }
        .listings-wrap { grid-template-columns: 1fr; padding: 90px 16px 50px; gap: 24px; }
        .career-sidebar { position: static; }
        .results-count { font-size: 20px; }
        .job { grid-template-columns: 1fr; gap: 12px; padding: 16px; margin: 0 -16px; }
        .job-summary { display: none; }
        .meta-detail { display: none; }
        .job-num { display: flex; gap: 10px; align-items: baseline; }
        .job-num strong { font-size: 18px; margin: 0; }
        .job-aside {
            flex-direction: row; align-items: center;
            justify-content: space-between; width: 100%;
            text-align: left;
            border-top: 1px dashed #d9ddd9; padding-top: 12px;
        }
        .pagination-wrap { justify-content: center; }
        .pagination-info { width: 100%; text-align: center; }
    }
    @media (max-width: 520px) {
        .hero-meta { flex-wrap: wrap; gap: 16px 22px; }
    }
</style>

{{-- Hidden filter form --}}
<form id="career-search" method="GET" action="{{ route('karier.index') }}"></form>

{{-- ─── HERO ────────────────────────────────────────────────── --}}
<section class="career-hero">
    <div class="hero-inner">
        {{-- Left: copy + stats --}}
        <div>
            <div class="hero-eyebrow">RS AZRA · Karir '26</div>
            <h1 class="career-h1">
                Bergabunglah dalam karya <em>penyembuhan</em> yang <span class="accent">bermakna</span>.
            </h1>
            <p class="hero-lede">
                Dua puluh delapan tahun. Tiga kampus. Lebih dari seribu staf yang hadir sebelum fajar
                agar seseorang bisa pulang dengan sehat. Kami mencari orang-orang yang berdiri di sisi mereka.
            </p>
            <div class="hero-meta">
                <div class="hero-stat"><strong>{{ $totalRoles }}</strong>Posisi terbuka</div>
                <div class="hero-stat"><strong>1.140+</strong>Staf profesional</div>
                <div class="hero-stat"><strong>3</strong>Kampus RS</div>
                <div class="hero-stat"><strong>96%</strong>Tingkat retensi</div>
            </div>
        </div>

        {{-- Right: abstract illustration --}}
        <div class="hero-illo">
            <svg viewBox="0 0 480 360" preserveAspectRatio="xMidYMid meet" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="hero-dots" x="0" y="0" width="14" height="14" patternUnits="userSpaceOnUse">
                        <circle cx="1" cy="1" r="1" fill="rgb(0,119,116)" opacity="0.18"/>
                    </pattern>
                    <pattern id="hero-lines" x="0" y="0" width="8" height="8" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">
                        <line x1="0" y1="0" x2="0" y2="8" stroke="rgb(129,189,65)" stroke-width="1" opacity="0.35"/>
                    </pattern>
                </defs>
                <rect x="20" y="20" width="440" height="320" fill="url(#hero-dots)"/>
                <circle cx="170" cy="180" r="120" fill="rgb(0,119,116)"/>
                <circle cx="310" cy="200" r="90" fill="rgb(129,189,65)" opacity="0.92"/>
                <rect x="320" y="60" width="120" height="80" fill="url(#hero-lines)"/>
                <rect x="320" y="60" width="120" height="80" fill="none" stroke="rgb(0,119,116)" stroke-width="1.2"/>
                <rect x="60" y="290" width="40" height="40" fill="#0d1614"/>
                <g transform="translate(170 180)">
                    <rect x="-10" y="-44" width="20" height="88" fill="white" opacity="0.95" rx="1.5"/>
                    <rect x="-44" y="-10" width="88" height="20" fill="white" opacity="0.95" rx="1.5"/>
                </g>
                <circle cx="400" cy="270" r="34" fill="none" stroke="#0d1614" stroke-width="1.4"/>
                <circle cx="400" cy="270" r="3" fill="#0d1614"/>
                <line x1="20"  y1="345" x2="20"  y2="335" stroke="#5a6864" stroke-width="1"/>
                <line x1="48"  y1="345" x2="48"  y2="340" stroke="#5a6864" stroke-width="1"/>
                <line x1="76"  y1="345" x2="76"  y2="340" stroke="#5a6864" stroke-width="1"/>
                <line x1="104" y1="345" x2="104" y2="340" stroke="#5a6864" stroke-width="1"/>
                <line x1="132" y1="345" x2="132" y2="335" stroke="#5a6864" stroke-width="1"/>
                <line x1="160" y1="345" x2="160" y2="340" stroke="#5a6864" stroke-width="1"/>
                <line x1="188" y1="345" x2="188" y2="340" stroke="#5a6864" stroke-width="1"/>
                <line x1="216" y1="345" x2="216" y2="340" stroke="#5a6864" stroke-width="1"/>
                <line x1="244" y1="345" x2="244" y2="335" stroke="#5a6864" stroke-width="1"/>
                <line x1="272" y1="345" x2="272" y2="340" stroke="#5a6864" stroke-width="1"/>
                <line x1="300" y1="345" x2="300" y2="340" stroke="#5a6864" stroke-width="1"/>
                <line x1="328" y1="345" x2="328" y2="340" stroke="#5a6864" stroke-width="1"/>
                <line x1="356" y1="345" x2="356" y2="335" stroke="#5a6864" stroke-width="1"/>
                <line x1="384" y1="345" x2="384" y2="340" stroke="#5a6864" stroke-width="1"/>
                <line x1="412" y1="345" x2="412" y2="340" stroke="#5a6864" stroke-width="1"/>
                <line x1="440" y1="345" x2="440" y2="340" stroke="#5a6864" stroke-width="1"/>
            </svg>
        </div>
    </div>

    {{-- Straddling search panel --}}
    <div class="search-panel">
        <div class="search-card">
            <div class="search-field">
                <label for="q-input">Posisi atau kata kunci</label>
                <input
                    form="career-search"
                    type="text"
                    id="q-input"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="mis. perawat ICU, radiolog, apoteker..."
                >
            </div>
            <button form="career-search" type="submit" class="search-submit">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                Temukan Posisi
            </button>
        </div>
    </div>
</section>

{{-- ─── LISTINGS + SIDEBAR ─────────────────────────────────── --}}
<div class="listings-wrap">

    {{-- ── Sidebar ─────────────────────────────────────────── --}}
    <aside class="career-sidebar">
        <h3 class="filter-h3">Saring</h3>

        {{-- Department filter --}}
        <div class="filter-group">
            <div class="filter-group-h4">Departemen</div>
            @forelse ($units as $unit)
                <label class="filter-chk">
                    <input
                        form="career-search"
                        type="checkbox"
                        name="unit[]"
                        value="{{ $unit->id }}"
                        {{ in_array($unit->id, $unitFilter ?? []) ? 'checked' : '' }}
                        x-on:change="document.getElementById('career-search').submit()"
                    >
                    <span>{{ $unit->nama }}</span>
                    <span class="count">{{ $unit->published_count }}</span>
                </label>
            @empty
                <p style="font-size: 12px; color: #8a948f;">Tidak ada departemen.</p>
            @endforelse
        </div>

        {{-- Employment type filter --}}
        <div class="filter-group">
            <div class="filter-group-h4">Jenis Pekerjaan</div>
            @foreach ($employmentTypes as $type)
                <label class="filter-chk">
                    <input
                        form="career-search"
                        type="checkbox"
                        name="type[]"
                        value="{{ $type->value }}"
                        {{ in_array($type->value, $typeFilter ?? []) ? 'checked' : '' }}
                        x-on:change="document.getElementById('career-search').submit()"
                    >
                    <span>{{ $type->label() }}</span>
                    <span class="count">{{ $typeCounts[$type->value] ?? 0 }}</span>
                </label>
            @endforeach
        </div>

        @if (request()->hasAny(['q', 'unit', 'type']))
            <a href="{{ route('karier.index') }}" style="font-size: 12px; color: rgb(0,119,116); text-decoration: underline; text-underline-offset: 3px;">
                ← Reset semua filter
            </a>
        @endif
    </aside>

    {{-- ── Job listings ────────────────────────────────────── --}}
    <div>
        {{-- Results header --}}
        <div class="results-head">
            <div class="results-count">
                <em>{{ $vacancies->total() }}</em> posisi terbuka
                @if(request('q'))
                    <span style="font-family: 'IBM Plex Mono', monospace; font-size: 12px; color: #8a948f; margin-left: 14px; text-transform: uppercase; letter-spacing: 0.06em;">
                        cocok "{{ request('q') }}"
                    </span>
                @endif
            </div>
        </div>

        @if ($vacancies->isEmpty())
            {{-- Empty state --}}
            <div style="padding: 60px 0; text-align: center; color: #5a6864;">
                <div style="width: 56px; height: 56px; border-radius: 50%; background: #e5f1f0; color: rgb(0,119,116); display: grid; place-items: center; margin: 0 auto 20px;">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z"/></svg>
                </div>
                <h3 style="font-family: 'IBM Plex Serif', serif; font-size: 22px; color: #0d1614; margin: 12px 0 6px; font-weight: 500;">Tidak ada lowongan yang cocok.</h3>
                <p style="font-size: 13.5px; max-width: 44ch; margin: 0 auto;">Coba kata kunci atau filter yang berbeda, atau reset filter untuk melihat semua posisi.</p>
                @if (request()->hasAny(['q', 'unit', 'type']))
                    <a href="{{ route('karier.index') }}" style="margin-top: 18px; display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; background: rgb(0,119,116); color: white; font-size: 13px; font-weight: 600; border-radius: 2px; text-decoration: none;">
                        Lihat semua lowongan
                    </a>
                @endif
            </div>
        @else
            {{-- Job rows --}}
            <div class="jobs">
                @foreach ($vacancies as $vacancy)
                    @php
                        $rowNum  = ($vacancies->currentPage() - 1) * $vacancies->perPage() + $loop->iteration;
                        $isNew    = $vacancy->created_at->gte(now()->subDays(3));
                        $isUrgent = $vacancy->tenggat_lamaran->lte(now()->addDays(7));
                        $summary  = \Illuminate\Support\Str::limit(strip_tags($vacancy->deskripsi_pekerjaan ?? ''), 140);
                    @endphp
                    <article class="job">
                        {{-- Number --}}
                        <div class="job-num">
                            <strong>{{ str_pad($rowNum, 2, '0', STR_PAD_LEFT) }}</strong>
                        </div>

                        {{-- Body --}}
                        <div class="job-body">
                            <h3 class="job-title">
                                <a href="{{ route('karier.show', $vacancy) }}">{{ $vacancy->judul_posisi }}</a>
                            </h3>
                            <div class="job-tags">
                                <span class="job-tag dept">{{ $vacancy->unit->nama }}</span>
                                <span class="job-tag">{{ $vacancy->jenis_pekerjaan->label() }}</span>
                                @if ($isNew)
                                    <span class="job-tag new">Baru</span>
                                @endif
                                @if ($isUrgent)
                                    <span class="job-tag urgent">Mendesak</span>
                                @endif
                            </div>
                            @if ($summary)
                                <p class="job-summary" style="margin: 0 0 10px; color: #2a3835; font-size: 13.5px; line-height: 1.55; max-width: 60ch;">{{ $summary }}</p>
                            @endif
                            <div class="job-meta">
                                <span>Ditayangkan {{ $vacancy->created_at->locale('id')->diffForHumans() }}</span>
                                <span class="meta-detail">&nbsp;·&nbsp; Tenggat {{ $vacancy->tenggat_lamaran->format('d M Y') }}</span>
                            </div>
                        </div>

                        {{-- Aside: apply CTA --}}
                        <div class="job-aside">
                            <a href="{{ route('karier.show', $vacancy) }}" class="apply-btn">
                                Lamar
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="pagination-wrap">
                <div class="pagination-info">
                    Menampilkan <strong>{{ $vacancies->firstItem() }}–{{ $vacancies->lastItem() }}</strong>
                    dari <strong>{{ $vacancies->total() }}</strong> posisi
                </div>
                <div>
                    {{ $vacancies->links() }}
                </div>
            </div>
        @endif
    </div>

</div>

</x-layouts.public>
