<x-layouts.app title="Dashboard - ATS RS Azra">
    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-2">
            Selamat datang, {{ auth()->user()->name }}!
        </h1>
        <p class="text-gray-600">
            Anda masuk sebagai <strong>{{ auth()->user()->role->label() }}</strong>.
        </p>
    </div>
</x-layouts.app>
