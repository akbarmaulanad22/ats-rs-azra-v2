<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Employee::class);

        $employees = Employee::query()
            ->when(
                $request->q,
                fn ($q, $search) => $q->where(function ($q) use ($search) {
                    $lower = strtolower($search);
                    $q->whereRaw('LOWER(nama_karyawan) LIKE ?', ["%{$lower}%"])
                        ->orWhereRaw('LOWER(nip) LIKE ?', ["%{$lower}%"]);
                }),
            )
            ->when($request->unit, fn ($q, $unit) => $q->where('unit', $unit))
            ->when(
                $request->posisi,
                fn ($q, $posisi) => $q->where('posisi_pekerjaan', $posisi),
            )
            ->when(
                $request->profesi,
                fn ($q, $profesi) => $q->where('profesi', $profesi),
            )
            ->when(
                $request->jabatan,
                fn ($q, $jabatan) => $q->where('jabatan', $jabatan),
            )
            ->orderBy('nama_karyawan')
            ->paginate(15)
            ->withQueryString();

        $filters = [
            'units' => Employee::distinct()->orderBy('unit')->pluck('unit'),
            'posisi' => Employee::distinct()
                ->orderBy('posisi_pekerjaan')
                ->pluck('posisi_pekerjaan'),
            'profesi' => Employee::distinct()
                ->orderBy('profesi')
                ->pluck('profesi'),
            'jabatan' => Employee::distinct()
                ->orderBy('jabatan')
                ->pluck('jabatan'),
        ];

        return view('employees.index', compact('employees', 'filters'));
    }

    public function create(): View
    {
        Gate::authorize('create', Employee::class);

        return view('employees.create');
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        Employee::create($request->validated());

        return redirect()
            ->route('karyawan.index')
            ->with('status', 'Data karyawan berhasil ditambahkan.');
    }

    public function show(Employee $employee): View
    {
        Gate::authorize('view', $employee);

        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee): View
    {
        Gate::authorize('update', $employee);

        return view('employees.edit', compact('employee'));
    }

    public function update(
        UpdateEmployeeRequest $request,
        Employee $employee,
    ): RedirectResponse {
        $employee->update($request->validated());

        return redirect()
            ->route('karyawan.index')
            ->with('status', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        Gate::authorize('delete', $employee);

        $employee->delete();

        return redirect()
            ->route('karyawan.index')
            ->with('status', 'Data karyawan berhasil dihapus.');
    }
}
