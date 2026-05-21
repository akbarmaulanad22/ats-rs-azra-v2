<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);

        $accounts = User::with('employee')
            ->when($request->q, function ($q, $search) {
                $lower = strtolower($search);
                $q->where(function ($q) use ($lower) {
                    $q->whereRaw('LOWER(username) LIKE ?', ["%{$lower}%"])
                        ->orWhereHas('employee', fn ($q) => $q
                            ->whereRaw('LOWER(nama_karyawan) LIKE ?', ["%{$lower}%"])
                            ->orWhereRaw('LOWER(nip) LIKE ?', ["%{$lower}%"])
                        );
                });
            })
            ->when($request->role, fn ($q, $role) => $q->where('role', $role))
            ->when($request->status === 'aktif', fn ($q) => $q->where('is_active', true))
            ->when($request->status === 'nonaktif', fn ($q) => $q->where('is_active', false))
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $roles = Role::cases();

        return view('accounts.index', compact('accounts', 'roles'));
    }

    public function searchAvailableEmployees(Request $request): JsonResponse
    {
        Gate::authorize('create', User::class);

        $q = strtolower(str_replace(['%', '_'], ['\\%', '\\_'], $request->string('q')));
        $query = Employee::whereNull('user_id')
            ->when($q, fn ($query) => $query->where(function ($query) use ($q) {
                $query->whereRaw('LOWER(nama_karyawan) LIKE ?', ["%{$q}%"])
                    ->orWhereRaw('LOWER(nip) LIKE ?', ["%{$q}%"]);
            }))
            ->orderBy('nama_karyawan');

        $results = $query->limit(11)->get();
        $hasMore = $results->count() > 10;
        $results = $results->take(10)->map(fn ($e) => [
            'id' => $e->id,
            'label' => $e->nama_karyawan.' ('.$e->nip.')',
            'employeeName' => $e->nama_karyawan,
        ]);

        return response()->json(['results' => $results, 'has_more' => $hasMore]);
    }

    public function create(): View
    {
        Gate::authorize('create', User::class);

        $roles = Role::cases();

        return view('accounts.create', compact('roles'));
    }

    public function store(StoreAccountRequest $request): RedirectResponse
    {
        $employee = Employee::findOrFail($request->employee_id);

        $user = User::create([
            'name' => $employee->nama_karyawan,
            'username' => $request->username,
            'password' => $request->password,
            'role' => $request->role,
            'must_change_password' => true,
            'is_active' => true,
        ]);

        $employee->update(['user_id' => $user->id]);

        return redirect()
            ->route('akun.index')
            ->with('status', 'Akun berhasil dibuat untuk '.$employee->nama_karyawan.'.');
    }

    public function edit(User $user): View
    {
        Gate::authorize('update', $user);

        $roles = Role::cases();

        return view('accounts.edit', compact('user', 'roles'));
    }

    public function update(UpdateAccountRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        $data = [
            'username' => $validated['username'],
            'role' => $validated['role'],
        ];

        if (! empty($validated['password'])) {
            $data['password'] = $validated['password'];
            $data['must_change_password'] = true;
        }

        $user->update($data);

        return redirect()
            ->route('akun.index')
            ->with('status', 'Akun '.$user->employee?->nama_karyawan.' berhasil diperbarui.');
    }

    public function toggleAktif(User $user): RedirectResponse
    {
        Gate::authorize('update', $user);

        $user->update(['is_active' => ! $user->is_active]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->route('akun.index')
            ->with('status', 'Akun '.$user->employee?->nama_karyawan.' berhasil '.$status.'.');
    }

    public static function generateUsername(string $name): string
    {
        $words = array_filter(explode(' ', trim($name)));
        $first = reset($words);
        $last = end($words);

        $username = $first === $last ? $first : $first.$last;

        return preg_replace('/[^a-z0-9]/', '', Str::lower(Str::ascii($username)));
    }
}
