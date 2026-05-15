@props(['title' => 'Karier - RS Azra', 'mainClass' => 'max-w-5xl mx-auto px-4 py-8'])

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:ital,wght@0,400;0,500;1,400&family=IBM+Plex+Sans:ital,wght@0,400;0,500;0,600;1,400&family=IBM+Plex+Serif:ital,wght@0,400;0,600;1,400;1,600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: "IBM Plex Sans", system-ui, sans-serif; color: #0d1614; }
        @media (min-width: 1024px) {
            .footer-grid { grid-template-columns: 1.4fr 1fr 1fr 1.3fr; }
        }
        .footer-overlay {
            position: absolute; inset: 0; pointer-events: none;
            background:
                radial-gradient(circle at 12% 100%, rgba(0,119,116,0.30) 0%, transparent 40%),
                radial-gradient(circle at 90% 10%,  rgba(129,189,65,0.18) 0%, transparent 50%);
        }
        .footer-contact-list { border-top: 1px solid rgba(255,255,255,0.1); padding-top: 16px; }
        .footer-contact-list div { display: flex; align-items: flex-start; gap: 10px; padding: 6px 0; font-size: 13px; color: #b8c0bd; }
        .footer-contact-list svg { width: 14px; height: 14px; color: rgb(129,189,65); margin-top: 3px; flex-shrink: 0; }
        .footer-contact-list a { color: #b8c0bd; text-decoration: none; transition: color 0.15s; }
        .footer-contact-list a:hover { color: white; }
        .footer-newsletter { display: flex; gap: 0; margin-top: 12px; border: 1px solid rgba(255,255,255,0.2); border-radius: 3px; overflow: hidden; }
        .footer-newsletter input { flex: 1; background: rgba(255,255,255,0.05); border: 0; color: white; padding: 11px 14px; font-size: 13px; outline: none; min-width: 0; font-family: inherit; }
        .footer-newsletter input::placeholder { color: rgba(255,255,255,0.4); }
        .footer-newsletter input:focus { background: rgba(255,255,255,0.10); }
        .footer-newsletter button { background: rgb(129,189,65); color: #0d1614; border: 0; padding: 11px 18px; font-size: 13px; font-weight: 600; cursor: pointer; font-family: inherit; }
        .footer-newsletter button:hover { background: rgb(103,153,50); color: white; }
        .footer-socials { display: flex; gap: 8px; margin-top: 18px; }
        .footer-socials a { width: 34px; height: 34px; border: 1px solid rgba(255,255,255,0.2); border-radius: 50%; display: grid; place-items: center; color: #cfd6d3; transition: all 0.15s; text-decoration: none; }
        .footer-socials a:hover { background: rgb(129,189,65); color: #0d1614; border-color: rgb(129,189,65); }
        .footer-socials svg { width: 14px; height: 14px; }
        .nav-link {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 8px 12px; font-size: 13px; font-weight: 500; color: #2a3835;
            border-radius: 6px; transition: background 0.15s, color 0.15s;
            white-space: nowrap;
        }
        .nav-link:hover { background: #f7f6f1; color: #0d1614; }
        .nav-link.active { color: rgb(0,119,116); }
        .dropdown-item {
            display: block; padding: 8px 16px; font-size: 13px; color: #2a3835;
            transition: background 0.1s, color 0.1s;
        }
        .dropdown-item:hover { background: #f7f6f1; color: #0d1614; }
        .mobile-nav-link {
            display: block; padding: 10px 0; font-size: 14px; color: #2a3835;
            border-bottom: 1px solid #ebeeea;
        }
        .mobile-nav-link:hover { color: #0d1614; }
    </style>
</head>
<body class="min-h-screen bg-paper">

    {{-- Utility bar --}}
    <div style="background: rgb(0,119,116);" class="text-white text-sm">
        <div class="max-w-[1320px] mx-auto px-6 py-3 flex items-center justify-between gap-4">
            <span class="italic text-white hidden sm:block">"Cepat, Ramah, Berkualitas"</span>
            <div class="flex items-center gap-5 ml-auto">
                <span class="text-white/80">(0251) 8382417</span>
                <span class="text-white/40 hidden sm:inline">|</span>
                <div class="flex items-center gap-3">
                    {{-- Instagram --}}
                    <a href="https://instagram.com/rsazra" target="_blank" rel="noopener" class="text-white/60 hover:text-white transition-colors">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                    </a>
                    {{-- Facebook --}}
                    <a href="https://facebook.com/rsazra" target="_blank" rel="noopener" class="text-white/60 hover:text-white transition-colors">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    {{-- YouTube --}}
                    <a href="https://youtube.com/@rsazra" target="_blank" rel="noopener" class="text-white/60 hover:text-white transition-colors">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.495 6.205a3.007 3.007 0 0 0-2.088-2.088c-1.87-.501-9.396-.501-9.396-.501s-7.507-.01-9.396.501A3.007 3.007 0 0 0 .527 6.205a31.247 31.247 0 0 0-.522 5.805 31.247 31.247 0 0 0 .522 5.783 3.007 3.007 0 0 0 2.088 2.088c1.868.502 9.396.502 9.396.502s7.506 0 9.396-.502a3.007 3.007 0 0 0 2.088-2.088 31.247 31.247 0 0 0 .5-5.783 31.247 31.247 0 0 0-.5-5.805zM9.609 15.601V8.408l6.264 3.602z"/></svg>
                    </a>
                    {{-- WhatsApp --}}
                    <a href="https://wa.me/6281219801997" target="_blank" rel="noopener" class="text-white/60 hover:text-white transition-colors">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Sticky header --}}
    <header
        x-data="{ menuOpen: false }"
        class="sticky top-0 z-50 bg-white border-b border-line shadow-sm"
    >
        <div class="max-w-[1320px] mx-auto px-6 flex h-20 items-center gap-2">
            {{-- Logo --}}
            <a href="https://rsazra.co.id" class="shrink-0 flex items-center mr-4">
                <img
                    src="https://rsazra.co.id/images/icon/logo.png"
                    alt="RS AZRA"
                    class="h-14 w-auto"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'"
                >
                <div class="hidden items-center gap-2">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-white font-bold text-sm" style="background:rgb(0,119,116)">A</div>
                    <span class="font-bold text-ink text-sm">RS AZRA</span>
                </div>
            </a>

            {{-- Desktop nav --}}
            <nav class="hidden lg:flex items-center flex-1 min-w-0">
                <a href="https://rsazra.co.id" class="nav-link">Beranda</a>
                <a href="https://rsazra.co.id/tentang-kami" class="nav-link">Tentang Kami</a>

                {{-- Layanan dropdown --}}
                <div class="relative" x-data="{ open: false }" @mouseenter="open=true" @mouseleave="open=false">
                    <button class="nav-link">
                        Layanan
                        <svg class="w-3 h-3 text-ink-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-1"
                        class="absolute top-full left-0 mt-1 bg-white border border-line rounded-lg shadow-lg py-1 w-44 z-50"
                    >
                        <a href="https://rsazra.co.id/layanan/rawat-inap" class="dropdown-item">Rawat Inap</a>
                        <a href="https://rsazra.co.id/layanan/rawat-jalan" class="dropdown-item">Rawat Jalan</a>
                        <a href="https://rsazra.co.id/layanan/penunjang-medis" class="dropdown-item">Penunjang Medis</a>
                    </div>
                </div>

                <a href="https://rsazra.co.id/jadwal-dokter" class="nav-link">Jadwal Dokter</a>
                <a href="https://rsazra.co.id/pusat-layanan" class="nav-link">Pusat Layanan</a>
                <a href="https://rsazra.co.id/fasilitas" class="nav-link">Fasilitas</a>

                {{-- Informasi Lainnya dropdown --}}
                <div class="relative" x-data="{ open: false }" @mouseenter="open=true" @mouseleave="open=false">
                    <button class="nav-link active">
                        Informasi Lainnya
                        <svg class="w-3 h-3 text-ink-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-1"
                        class="absolute top-full left-0 mt-1 bg-white border border-line rounded-lg shadow-lg py-1 w-44 z-50"
                    >
                        <a href="https://rsazra.co.id/berita" class="dropdown-item">Berita</a>
                        <a href="{{ route('karier.index') }}" class="dropdown-item font-semibold" style="color: rgb(0,119,116)">Karir</a>
                        <a href="https://rsazra.co.id/promo" class="dropdown-item">Promo</a>
                        <a href="https://rsazra.co.id/kerjasama" class="dropdown-item">Kerjasama</a>
                    </div>
                </div>
            </nav>

            {{-- Right: CTA + hamburger --}}
            <div class="flex items-center gap-3 ml-auto">
                <a href="{{ route('login') }}" class="hidden lg:inline-flex items-center" style="padding:11px 20px; border:1px solid #0d1614; background:white; color:#0d1614; font-size:13px; font-weight:600; cursor:pointer; font-family:'IBM Plex Sans',system-ui,sans-serif; transition:background 0.15s; border-radius:0;" onmouseover="this.style.background='#efede5'" onmouseout="this.style.background='white'">
                    Login
                </a>
                <button
                    @click="menuOpen = !menuOpen"
                    class="lg:hidden p-2 rounded-md text-ink-3 hover:text-ink hover:bg-paper-2 transition-colors"
                    aria-label="Buka menu"
                >
                    <svg x-show="!menuOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg x-show="menuOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        {{-- Mobile drawer --}}
        <div
            x-show="menuOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="lg:hidden border-t border-line bg-white"
        >
            <nav class="max-w-[1320px] mx-auto px-6 py-2 pb-4">
                <a href="https://rsazra.co.id" class="mobile-nav-link">Beranda</a>
                <a href="https://rsazra.co.id/tentang-kami" class="mobile-nav-link">Tentang Kami</a>
                <a href="https://rsazra.co.id/layanan/rawat-inap" class="mobile-nav-link">Rawat Inap</a>
                <a href="https://rsazra.co.id/layanan/rawat-jalan" class="mobile-nav-link">Rawat Jalan</a>
                <a href="https://rsazra.co.id/layanan/penunjang-medis" class="mobile-nav-link">Penunjang Medis</a>
                <a href="https://rsazra.co.id/jadwal-dokter" class="mobile-nav-link">Jadwal Dokter</a>
                <a href="https://rsazra.co.id/pusat-layanan" class="mobile-nav-link">Pusat Layanan</a>
                <a href="https://rsazra.co.id/fasilitas" class="mobile-nav-link">Fasilitas</a>
                <a href="https://rsazra.co.id/berita" class="mobile-nav-link">Berita</a>
                <a href="{{ route('karier.index') }}" class="mobile-nav-link font-semibold" style="color:rgb(0,119,116)">Karir</a>
                <a href="https://rsazra.co.id/promo" class="mobile-nav-link">Promo</a>
                <div class="pt-4">
                    <a href="{{ route('login') }}" class="inline-flex items-center" style="padding:11px 20px; border:1px solid #0d1614; background:white; color:#0d1614; font-size:13px; font-weight:600; cursor:pointer; font-family:'IBM Plex Sans',system-ui,sans-serif; transition:background 0.15s; border-radius:0;" onmouseover="this.style.background='#efede5'" onmouseout="this.style.background='white'">
                        Login
                    </a>
                </div>
            </nav>
        </div>
    </header>

    <main class="{{ $mainClass }}">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer style="background: #0c1d1c; color: #cfd6d3; margin-top: 40px; position: relative; overflow: hidden;" class="text-white">
        {{-- Radial gradient atmosphere --}}
        <div class="footer-overlay"></div>

        {{-- Main columns --}}
        <div class="max-w-[1320px] mx-auto px-7 relative z-10" style="padding-top: 56px; padding-bottom: 28px;">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10 footer-grid" style="gap: 48px;">

                {{-- Brand + contact --}}
                <div>
                    <img
                        src="https://rsazra.co.id/images/icon/logo.png"
                        alt="RS AZRA"
                        class="w-auto mb-0 brightness-0 invert"
                        style="height: 52px;"
                        onerror="this.style.display='none'; this.nextElementSibling.style.display='block'"
                    >
                    <span class="hidden text-white font-bold text-lg">RS AZRA</span>
                    <p style="color:#b8c0bd; font-size:13px; line-height:1.65; max-width:38ch; margin:16px 0 18px;">
                        Rumah Sakit AZRA Bogor menyediakan layanan kesehatan lengkap 24 jam dengan dokter spesialis, IGD, rawat inap, dan fasilitas modern terpercaya di Bogor.
                    </p>
                    <div class="footer-contact-list">
                        <div>
                            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                            <span>Jl. Pintu Air No.1, Sempur, Bogor Tengah, Kota Bogor, Jawa Barat 16112</span>
                        </div>
                        <div>
                            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                            <a href="tel:02518382417">(0251) 8382417 / 8382419</a>
                        </div>
                        <div>
                            <svg fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            <a href="https://wa.me/6281219801997">WA 0812 1980 1997</a>
                        </div>
                        <div>
                            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                            <a href="mailto:rsazra@gmail.com">rsazra@gmail.com</a>
                        </div>
                    </div>
                </div>

                {{-- Informasi Pengunjung --}}
                <div>
                    <h5 style="font-size:12px; text-transform:uppercase; letter-spacing:0.12em; color:white; margin:0 0 18px; font-weight:600; font-family:'IBM Plex Sans',system-ui,sans-serif;">Informasi Pengunjung</h5>
                    <ul style="margin:0; padding:0; list-style:none;">
                        <li style="padding:5px 0;"><a href="https://rsazra.co.id/jadwal-dokter" style="color:#b8c0bd; text-decoration:none; font-size:13.5px; transition:color 0.15s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#b8c0bd'">Jadwal Dokter</a></li>
                        <li style="padding:5px 0;"><a href="https://rsazra.co.id/fasilitas" style="color:#b8c0bd; text-decoration:none; font-size:13.5px;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#b8c0bd'">Fasilitas</a></li>
                        <li style="padding:5px 0;"><a href="https://rsazra.co.id/layanan/rawat-inap" style="color:#b8c0bd; text-decoration:none; font-size:13.5px;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#b8c0bd'">Rawat Inap</a></li>
                        <li style="padding:5px 0;"><a href="https://rsazra.co.id/layanan/rawat-jalan" style="color:#b8c0bd; text-decoration:none; font-size:13.5px;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#b8c0bd'">Rawat Jalan</a></li>
                        <li style="padding:5px 0;"><a href="https://rsazra.co.id/layanan/penunjang-medis" style="color:#b8c0bd; text-decoration:none; font-size:13.5px;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#b8c0bd'">Penunjang Medis</a></li>
                        <li style="padding:5px 0;"><a href="https://rsazra.co.id/pusat-layanan" style="color:#b8c0bd; text-decoration:none; font-size:13.5px;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#b8c0bd'">Pusat Layanan</a></li>
                    </ul>
                </div>

                {{-- Perusahaan --}}
                <div>
                    <h5 style="font-size:12px; text-transform:uppercase; letter-spacing:0.12em; color:white; margin:0 0 18px; font-weight:600; font-family:'IBM Plex Sans',system-ui,sans-serif;">Perusahaan</h5>
                    <ul style="margin:0; padding:0; list-style:none;">
                        <li style="padding:5px 0;"><a href="https://rsazra.co.id/tentang-kami" style="color:#b8c0bd; text-decoration:none; font-size:13.5px;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#b8c0bd'">Tentang Kami</a></li>
                        <li style="padding:5px 0;"><a href="{{ route('karier.index') }}" style="color:white; text-decoration:none; font-size:13.5px; font-weight:600;">Karir</a></li>
                        <li style="padding:5px 0;"><a href="https://rsazra.co.id/kerjasama" style="color:#b8c0bd; text-decoration:none; font-size:13.5px;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#b8c0bd'">Kerjasama Perusahaan</a></li>
                        <li style="padding:5px 0;"><a href="https://rsazra.co.id/berita" style="color:#b8c0bd; text-decoration:none; font-size:13.5px;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#b8c0bd'">Berita &amp; Artikel</a></li>
                        <li style="padding:5px 0;"><a href="https://rsazra.co.id/promo" style="color:#b8c0bd; text-decoration:none; font-size:13.5px;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#b8c0bd'">Promo</a></li>
                    </ul>
                </div>

                {{-- Newsletter + Social --}}
                <div>
                    <h5 style="font-size:12px; text-transform:uppercase; letter-spacing:0.12em; color:white; margin:0 0 18px; font-weight:600; font-family:'IBM Plex Sans',system-ui,sans-serif;">Kabar Terbaru</h5>
                    <p style="font-size:13px; color:#b8c0bd; line-height:1.6; margin:0 0 4px;">
                        Daftarkan e-mail Anda untuk berlangganan newsletter dan informasi terbaru dari RS AZRA.
                    </p>
                    <form class="footer-newsletter" onsubmit="return false;">
                        <input type="email" placeholder="Alamat email Anda">
                        <button type="submit">Subscribe</button>
                    </form>

                    <h5 style="font-size:12px; text-transform:uppercase; letter-spacing:0.12em; color:white; margin:24px 0 0; font-weight:600; font-family:'IBM Plex Sans',system-ui,sans-serif;">Ikuti Kami</h5>
                    <div class="footer-socials">
                        <a href="https://instagram.com/rsazra" target="_blank" rel="noopener" aria-label="Instagram">
                            <svg fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        <a href="https://facebook.com/rsazra" target="_blank" rel="noopener" aria-label="Facebook">
                            <svg fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="https://youtube.com/@rsazra" target="_blank" rel="noopener" aria-label="YouTube">
                            <svg fill="currentColor" viewBox="0 0 24 24"><path d="M23.495 6.205a3.007 3.007 0 0 0-2.088-2.088c-1.87-.501-9.396-.501-9.396-.501s-7.507-.01-9.396.501A3.007 3.007 0 0 0 .527 6.205a31.247 31.247 0 0 0-.522 5.805 31.247 31.247 0 0 0 .522 5.783 3.007 3.007 0 0 0 2.088 2.088c1.868.502 9.396.502 9.396.502s7.506 0 9.396-.502a3.007 3.007 0 0 0 2.088-2.088 31.247 31.247 0 0 0 .5-5.783 31.247 31.247 0 0 0-.5-5.805zM9.609 15.601V8.408l6.264 3.602z"/></svg>
                        </a>
                        <a href="https://tiktok.com/@rsazra" target="_blank" rel="noopener" aria-label="TikTok">
                            <svg fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.18 8.18 0 004.78 1.52V6.74a4.85 4.85 0 01-1.01-.05z"/></svg>
                        </a>
                    </div>
                </div>

            </div>
        </div>

        {{-- Footer bottom bar --}}
        <div style="border-top: 1px solid rgba(255,255,255,0.1); position: relative; z-index: 10;">
            <div class="max-w-[1320px] mx-auto px-7 py-5 flex justify-between flex-wrap gap-3" style="font-family:'IBM Plex Mono',monospace; font-size:11px; color:#6c7773; letter-spacing:0.04em;">
                <span>Copyright © {{ date('Y') }} RS Azra Group. All Rights Reserved.</span>
                <span>KARIR · RS AZRA BOGOR</span>
            </div>
        </div>
    </footer>

</body>
</html>
