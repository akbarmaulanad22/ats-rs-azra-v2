<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tes Selesai - ATS RS Azra</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">

    <div class="max-w-md w-full mx-4">
        <div class="bg-white rounded-2xl border border-gray-100 p-8 text-center shadow-sm">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </div>

            <h1 class="text-xl font-semibold text-gray-900 mb-2">Tes Berhasil Dikirim</h1>
            <p class="text-sm text-gray-500 mb-6">
                Jawaban Anda telah diterima. Tim HR akan meninjau hasil tes dan menghubungi Anda melalui email.
            </p>

            <div class="bg-gray-50 rounded-xl p-4 text-left space-y-2 mb-6">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Posisi</span>
                    <span class="font-medium text-gray-800">{{ $submission->application->vacancy->judul_posisi }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Waktu Pengiriman</span>
                    <span class="font-medium text-gray-800">{{ $submission->submitted_at->format('d M Y, H:i') }}</span>
                </div>
            </div>

            <p class="text-xs text-gray-400">Halaman ini dapat ditutup.</p>
        </div>
    </div>

</body>
</html>
