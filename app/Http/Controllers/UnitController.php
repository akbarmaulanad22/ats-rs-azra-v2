<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Unit::class);

        $units = Unit::query()
            ->when(
                $request->q,
                fn ($q, $search) => $q->whereRaw('LOWER(nama) LIKE ?', ['%'.strtolower(str_replace(['%', '_'], ['\%', '\_'], $search)).'%']),
            )
            ->orderBy('nama')
            ->paginate(15)
            ->withQueryString();

        return view('units.index', compact('units'));
    }

    public function search(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Unit::class);

        $q = strtolower(str_replace(['%', '_'], ['\\%', '\\_'], $request->string('q')));
        $query = Unit::when($q, fn ($query) => $query->whereRaw('LOWER(nama) LIKE ?', ["%{$q}%"]))
            ->orderBy('nama');

        $results = $query->limit(11)->get();
        $hasMore = $results->count() > 10;
        $results = $results->take(10)->map(fn ($u) => ['id' => $u->id, 'label' => $u->nama]);

        return response()->json(['results' => $results, 'has_more' => $hasMore]);
    }

    public function create(): View
    {
        Gate::authorize('create', Unit::class);

        return view('units.create');
    }

    public function store(StoreUnitRequest $request): RedirectResponse
    {
        Unit::create($request->validated());

        return redirect()
            ->route('unit.index')
            ->with('status', 'Data unit berhasil ditambahkan.');
    }

    public function edit(Unit $unit): View
    {
        Gate::authorize('update', $unit);

        return view('units.edit', compact('unit'));
    }

    public function update(UpdateUnitRequest $request, Unit $unit): RedirectResponse
    {
        $unit->update($request->validated());

        return redirect()
            ->route('unit.index')
            ->with('status', 'Data unit berhasil diperbarui.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        Gate::authorize('delete', $unit);

        $unit->delete();

        return redirect()
            ->route('unit.index')
            ->with('status', 'Data unit berhasil dihapus.');
    }
}
