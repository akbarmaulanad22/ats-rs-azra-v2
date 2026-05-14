<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Karier - RS Azra' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-page min-h-screen">

    <header class="bg-primary shadow-sm">
        <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo.jpg') }}" alt="RS Azra" class="w-9 h-9 rounded-xl object-cover ring-2 ring-white/20">
                <div>
                    <p class="text-white font-bold text-sm leading-tight">RS AZRA</p>
                    <p class="text-white/60 text-xs">Karir & Lowongan</p>
                </div>
            </div>
            @auth
                <a href="{{ route('dashboard') }}" class="text-sm text-white/80 hover:text-white transition-colors ease-out duration-150">
                    Ke Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="text-sm text-white/80 hover:text-white transition-colors ease-out duration-150">
                    Masuk
                </a>
            @endauth
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 py-8">
        {{ $slot }}
    </main>

    <footer class="mt-16 border-t border-gray-200 py-6">
        <p class="text-center text-xs text-gray-400">© {{ date('Y') }} Rumah Sakit Azra. Semua hak dilindungi.</p>
    </footer>

</body>
</html>
