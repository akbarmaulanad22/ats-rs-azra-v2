<x-layouts.app title="Bank Soal - ATS RS Azra">

    <div class="mb-5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Bank Soal</h1>
                <p class="text-xs text-gray-500 mt-0.5">Kelola soal tes kompetensi berdasarkan unit</p>
            </div>
            <a
                href="{{ route('bank-soal.create') }}"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors ease-out duration-150"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Soal
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 px-4 py-2.5 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filters --}}
    <form method="GET" action="{{ route('bank-soal.index') }}" class="mb-4 flex flex-wrap items-center gap-2">
        <select name="unit_id" onchange="this.form.submit()"
            class="text-xs border border-gray-200 rounded-lg px-3 py-1.5 bg-white text-gray-700 focus:outline-none focus:ring-1 focus:ring-primary/40">
            <option value="">Semua Unit</option>
            @foreach ($units as $unit)
                <option value="{{ $unit->id }}" {{ $unitFilter == $unit->id ? 'selected' : '' }}>{{ $unit->nama }}</option>
            @endforeach
        </select>
        <select name="tipe" onchange="this.form.submit()"
            class="text-xs border border-gray-200 rounded-lg px-3 py-1.5 bg-white text-gray-700 focus:outline-none focus:ring-1 focus:ring-primary/40">
            <option value="">Semua Tipe</option>
            <option value="mc" {{ $typeFilter === 'mc' ? 'selected' : '' }}>Pilihan Ganda</option>
            <option value="essay" {{ $typeFilter === 'essay' ? 'selected' : '' }}>Esai</option>
        </select>
    </form>

    @if ($questions->isEmpty())
        <div class="bg-white rounded-xl border border-gray-100 px-6 py-12 text-center">
            <p class="text-sm text-gray-400">Belum ada soal. Klik "Tambah Soal" untuk mulai.</p>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="text-left text-xs font-medium text-gray-400 px-5 py-2.5">Soal</th>
                        <th class="text-left text-xs font-medium text-gray-400 px-5 py-2.5">Unit</th>
                        <th class="text-left text-xs font-medium text-gray-400 px-5 py-2.5">Tipe</th>
                        <th class="text-left text-xs font-medium text-gray-400 px-5 py-2.5">Poin</th>
                        <th class="text-right text-xs font-medium text-gray-400 px-5 py-2.5">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($questions as $question)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-5 py-3 text-gray-800 max-w-sm">
                                <p class="line-clamp-2">{{ $question->pertanyaan }}</p>
                            </td>
                            <td class="px-5 py-3 text-gray-600 whitespace-nowrap">{{ $question->unit->nama }}</td>
                            <td class="px-5 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ $question->tipe->value === 'mc' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $question->tipe->label() }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-gray-600 whitespace-nowrap">{{ $question->nilai_poin }}</td>
                            <td class="px-5 py-3 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('bank-soal.edit', $question) }}"
                                        class="text-xs text-gray-500 hover:text-primary transition-colors">Edit</a>
                                    <form method="POST" action="{{ route('bank-soal.destroy', $question) }}"
                                        onsubmit="return confirm('Hapus soal ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-400 hover:text-red-600 transition-colors">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $questions->links() }}
        </div>
    @endif

</x-layouts.app>
