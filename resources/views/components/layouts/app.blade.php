<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'ATS RS Azra' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-page min-h-screen" x-data="{ sidebarOpen: true }">

    {{-- Sidebar --}}
    <aside
        class="fixed inset-y-0 left-0 z-30 bg-primary flex flex-col ease-out duration-300 transition-[width] overflow-hidden"
        :class="sidebarOpen ? 'w-64' : 'w-0'"
    >
        {{-- SVG depth layers --}}
        <svg class="absolute inset-0 w-full h-full pointer-events-none" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="sb-dots" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                    <circle cx="1.5" cy="1.5" r="1" fill="white" opacity="0.07"/>
                </pattern>
            </defs>
            {{-- L1: dot grid --}}
            <rect width="100%" height="100%" fill="url(#sb-dots)"/>
            {{-- L2: top-right corner rings --}}
            <circle cx="280" cy="-30" r="180" fill="none" stroke="white" stroke-width="1"   opacity="0.07"/>
            <circle cx="280" cy="-30" r="110" fill="none" stroke="white" stroke-width="1.5" opacity="0.09"/>
            <circle cx="280" cy="-30" r="55"  fill="none" stroke="white" stroke-width="1"   opacity="0.07"/>
            {{-- L3: bottom-left corner rings --}}
            <circle cx="-20" cy="105%" r="160" fill="none" stroke="white" stroke-width="1"   opacity="0.06"/>
            <circle cx="-20" cy="105%" r="90"  fill="none" stroke="white" stroke-width="1"   opacity="0.08"/>
            {{-- L4: sweeping arc --}}
            <path d="M -20 800 Q 140 400 300 60" fill="none" stroke="white" stroke-width="0.75" opacity="0.07"/>
            {{-- L5: scattered cross marks --}}
            <g stroke="white" stroke-width="1.5" stroke-linecap="round" opacity="0.09">
                <line x1="38"  y1="200" x2="38"  y2="210"/> <line x1="33"  y1="205" x2="43"  y2="205"/>
                <line x1="210" y1="140" x2="210" y2="150"/> <line x1="205" y1="145" x2="215" y2="145"/>
                <line x1="55"  y1="520" x2="55"  y2="530"/> <line x1="50"  y1="525" x2="60"  y2="525"/>
                <line x1="190" y1="620" x2="190" y2="630"/> <line x1="185" y1="625" x2="195" y2="625"/>
                <line x1="120" y1="360" x2="120" y2="370"/> <line x1="115" y1="365" x2="125" y2="365"/>
            </g>
        </svg>

        {{-- Brand --}}
        <div class="flex items-center gap-3 px-5 py-4 border-b border-white/10 shrink-0">
            <img
                src="{{ asset('images/logo.jpg') }}"
                alt="RS Azra"
                class="w-10 h-10 rounded-xl object-cover shrink-0 ring-2 ring-white/20"
            >
            <div class="whitespace-nowrap overflow-hidden">
                <p class="text-white font-bold text-sm leading-tight">RS AZRA</p>
                <p class="text-white/50 text-xs mt-0.5">Sistem Rekrutmen</p>
            </div>
        </div>

        {{-- Nav items --}}
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <a
                href="{{ route('dashboard') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors ease-out duration-150 whitespace-nowrap
                    {{ request()->routeIs('dashboard') ? 'bg-secondary text-white font-semibold' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
            >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>Beranda</span>
            </a>

            @can('viewAny', App\Models\Employee::class)
            <a
                href="{{ route('karyawan.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors ease-out duration-150 whitespace-nowrap
                    {{ request()->routeIs('karyawan.*') ? 'bg-secondary text-white font-semibold' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
            >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Karyawan</span>
            </a>
            @endcan

            @can('viewAny', App\Models\Unit::class)
            <a
                href="{{ route('unit.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors ease-out duration-150 whitespace-nowrap
                    {{ request()->routeIs('unit.*') ? 'bg-secondary text-white font-semibold' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
            >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                </svg>
                <span>Unit</span>
            </a>
            @endcan

            @can('viewAny', App\Models\User::class)
            <a
                href="{{ route('akun.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors ease-out duration-150 whitespace-nowrap
                    {{ request()->routeIs('akun.*') ? 'bg-secondary text-white font-semibold' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
            >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
                <span>Akun Pengguna</span>
            </a>
            @endcan

            @can('viewAny', App\Models\WorkflowTemplate::class)
            <a
                href="{{ route('template-alur.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors ease-out duration-150 whitespace-nowrap
                    {{ request()->routeIs('template-alur.*') ? 'bg-secondary text-white font-semibold' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
            >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                <span>Template Alur Kerja</span>
            </a>
            @endcan

            @can('viewAny', App\Models\JobTemplate::class)
            <a
                href="{{ route('template-lowongan.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors ease-out duration-150 whitespace-nowrap
                    {{ request()->routeIs('template-lowongan.*') ? 'bg-secondary text-white font-semibold' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
            >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
                <span>Template Lowongan</span>
            </a>
            @endcan

            @can('viewAny', App\Models\Vacancy::class)
            <a
                href="{{ route('lowongan.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors ease-out duration-150 whitespace-nowrap
                    {{ request()->routeIs('lowongan.*') ? 'bg-secondary text-white font-semibold' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
            >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z" />
                </svg>
                <span>Lowongan Kerja</span>
            </a>
            @endcan

            @can('viewAny', App\Models\QuestionBankTemplate::class)
            <a
                href="{{ route('template-bank-soal.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors ease-out duration-150 whitespace-nowrap
                    {{ request()->routeIs('template-bank-soal.*') ? 'bg-secondary text-white font-semibold' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
            >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 012.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                </svg>
                <span>Template Bank Soal</span>
            </a>
            @endcan

            @can('viewAny', App\Models\EmailTemplate::class)
            <a
                href="{{ route('template-email.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors ease-out duration-150 whitespace-nowrap
                    {{ request()->routeIs('template-email.*') ? 'bg-secondary text-white font-semibold' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
            >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span>Template Email</span>
            </a>
            @endcan

            @can('viewAny', App\Models\InterviewTemplate::class)
            <a
                href="{{ route('template-wawancara.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors ease-out duration-150 whitespace-nowrap
                    {{ request()->routeIs('template-wawancara.*') ? 'bg-secondary text-white font-semibold' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
            >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                <span>Template Wawancara</span>
            </a>
            @endcan
        </nav>
    </aside>

    {{-- Header --}}
    <header
        class="fixed top-0 right-0 z-20 bg-white h-16 flex items-center px-4 ease-out duration-300 transition-[left]"
        :class="sidebarOpen ? 'left-64' : 'left-0'"
        style="box-shadow: 0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.05);"
    >
        {{-- Hamburger --}}
        <button
            @click="sidebarOpen = !sidebarOpen"
            class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors ease-out duration-150 mr-4"
            aria-label="Toggle sidebar"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <div class="flex-1"></div>

        {{-- Bell notification --}}
        @auth
        <a href="{{ route('notifikasi.index') }}" class="relative p-2 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors ease-out duration-150" aria-label="Notifikasi">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            @php $unreadCount = auth()->user()->unreadNotifications()->count(); @endphp
            @if ($unreadCount > 0)
            <span class="absolute top-1 right-1 inline-flex items-center justify-center w-4 h-4 text-xs font-bold text-white bg-red-500 rounded-full leading-none">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
            @endif
        </a>
        @endauth

        {{-- User info: name + role badge + logout --}}
        <div class="flex items-center gap-3">
            <span class="text-sm font-medium text-gray-800 hidden sm:block">{{ auth()->user()->name }}</span>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs bg-primary/10 text-primary whitespace-nowrap">
                {{ auth()->user()->role->label() }}
            </span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    type="submit"
                    class="text-sm text-gray-400 hover:text-red-600 transition-colors ease-out duration-150 px-2 py-1 rounded"
                >
                    Keluar
                </button>
            </form>
        </div>
    </header>

    {{-- Main content --}}
    <main
        class="pt-16 min-h-screen ease-out duration-300 transition-[margin-left]"
        :class="sidebarOpen ? 'ml-64' : 'ml-0'"
    >
        <div class="p-6">
            @if (session('status'))
                <div
                    class="mb-4 rounded-lg bg-green-50 border border-green-200 p-4 flex items-start justify-between gap-3 transition-opacity duration-500 cursor-alias"
                    x-data="{ show: true }"
                    x-show="show"
                    x-init="setTimeout(() => show = false, 2000)"
                >
                    <p class="text-sm text-green-700">{{ session('status') }}</p>
                    <button
                        @click="show = false"
                        class="text-green-500 hover:text-green-700 transition-colors shrink-0 -mt-0.5 cursor-pointer"
                        aria-label="Tutup"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            @endif

            @if (session('warning'))
                <div
                    class="mb-4 rounded-lg bg-amber-50 border border-amber-200 p-4 flex items-start justify-between gap-3 transition-opacity duration-500 cursor-alias"
                    x-data="{ show: true }"
                    x-show="show"
                    x-init="setTimeout(() => show = false, 4000)"
                >
                    <p class="text-sm text-amber-700">{{ session('warning') }}</p>
                    <button
                        @click="show = false"
                        class="text-amber-500 hover:text-amber-700 transition-colors shrink-0 -mt-0.5 cursor-pointer"
                        aria-label="Tutup"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            @endif

            {{ $slot }}
        </div>
    </main>

</body>
</html>
