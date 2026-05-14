<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'ATS RS Azra' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex">

    {{-- Left panel: branded, structured, editorial --}}
    <div class="hidden lg:flex lg:w-[52%] bg-gradient-to-br from-primary to-primary-dark relative overflow-hidden flex-col shrink-0">

        {{-- SVG depth layers: 6 distinct visual layers --}}
        <svg class="absolute inset-0 w-full h-full pointer-events-none" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="dots" x="0" y="0" width="24" height="24" patternUnits="userSpaceOnUse">
                    <circle cx="1.5" cy="1.5" r="1" fill="white" opacity="0.09"/>
                </pattern>
            </defs>

            {{-- L1: dot grid texture --}}
            <rect width="100%" height="100%" fill="url(#dots)"/>

            {{-- L2: top-right concentric rings --}}
            <circle cx="110%" cy="-8%" r="320" fill="none" stroke="white" stroke-width="1"   opacity="0.07"/>
            <circle cx="110%" cy="-8%" r="210" fill="none" stroke="white" stroke-width="1.5" opacity="0.09"/>
            <circle cx="110%" cy="-8%" r="120" fill="none" stroke="white" stroke-width="1"   opacity="0.07"/>

            {{-- L3: bottom-left rings --}}
            <circle cx="-8%" cy="108%" r="260" fill="none" stroke="white" stroke-width="1"   opacity="0.06"/>
            <circle cx="-8%" cy="108%" r="160" fill="none" stroke="white" stroke-width="1"   opacity="0.08"/>

            {{-- L4: sweeping bezier arcs (bottom-left → top-right diagonal flow) --}}
            <path d="M -100 920 Q 320 460 860  20" fill="none" stroke="white" stroke-width="1"   opacity="0.09"/>
            <path d="M -100 800 Q 320 340 860 -80" fill="none" stroke="white" stroke-width="0.75" opacity="0.06"/>

            {{-- L5: large center ring (structural anchor behind content) --}}
            <circle cx="50%" cy="52%" r="390" fill="none" stroke="white" stroke-width="1" opacity="0.04"/>

            {{-- L6: scattered cross marks (hospital cross motif, very subtle) --}}
            <g stroke="white" stroke-width="1.5" stroke-linecap="round" opacity="0.11">
                <line x1="77"  y1="162" x2="77"  y2="174"/> <line x1="71"  y1="168" x2="83"  y2="168"/>
                <line x1="330" y1="78"  x2="330" y2="90" /> <line x1="324" y1="84"  x2="336" y2="84" />
                <line x1="580" y1="210" x2="580" y2="222"/> <line x1="574" y1="216" x2="586" y2="216"/>
                <line x1="175" y1="430" x2="175" y2="442"/> <line x1="169" y1="436" x2="181" y2="436"/>
                <line x1="495" y1="375" x2="495" y2="387"/> <line x1="489" y1="381" x2="501" y2="381"/>
                <line x1="92"  y1="690" x2="92"  y2="702"/> <line x1="86"  y1="696" x2="98"  y2="696"/>
                <line x1="420" y1="640" x2="420" y2="652"/> <line x1="414" y1="646" x2="426" y2="646"/>
            </g>
        </svg>

        {{-- Content: logo top, heading middle, features bottom --}}
        <div class="relative z-10 flex-1 flex flex-col justify-between px-14 py-12">

            {{-- Institutional header --}}
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-white/15">
                    <svg class="w-4.5 h-4.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M8 2h4v6h6v4h-6v6H8v-6H2V8h6V2z"/>
                    </svg>
                </div>
                <span class="text-white/90 font-semibold text-sm tracking-wide">Rumah Sakit Azra</span>
            </div>

            {{-- Main statement --}}
            <div>
                <p class="text-white/50 text-xs font-medium tracking-widest uppercase mb-4">Sistem Kepegawaian</p>
                <h1 class="text-[3.25rem] font-bold text-white leading-[1.05] tracking-tight mb-5">
                    Applicant<br>Tracking<br>System
                </h1>
                <p class="text-white/60 text-sm leading-relaxed max-w-xs">
                    Kelola rekrutmen dan data kepegawaian Rumah Sakit Azra dari satu platform yang terintegrasi dan andal.
                </p>
            </div>

            {{-- Feature signals --}}
            <div class="border-t border-white/15 pt-6 space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-1.5 h-1.5 rounded-full bg-secondary shrink-0"></div>
                    <span class="text-white/70 text-xs">Manajemen rekrutmen terpadu</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-1.5 h-1.5 rounded-full bg-secondary shrink-0"></div>
                    <span class="text-white/70 text-xs">Kontrol akses berbasis peran</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-1.5 h-1.5 rounded-full bg-secondary shrink-0"></div>
                    <span class="text-white/70 text-xs">Data karyawan terpusat dan aman</span>
                </div>
            </div>

        </div>
    </div>

    {{-- Right panel: form area --}}
    <div class="flex-1 flex items-center justify-center bg-page px-6 py-12">
        <div class="w-full max-w-md">
            {{ $slot }}
        </div>
    </div>

</body>
</html>
