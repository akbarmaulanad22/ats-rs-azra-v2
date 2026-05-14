<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Models\Employee;
use App\Models\User;
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
                $q->where(function ($q) use ($search) {
                    $q->where('username', 'ilike', "%{$search}%")
                        ->orWhereHas('employee', fn ($q) => $q
                            ->where('nama_karyawan', 'ilike', "%{$search}%")
                            ->orWhere('nip', 'ilike', "%{$search}%")
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

    public function create(): View
    {
        Gate::authorize('create', User::class);

        $employees = Employee::whereNull('user_id')
            ->orderBy('nama_karyawan')
            ->get();

        $roles = Role::cases();

        return view('accounts.create', compact('employees', 'roles'));
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
