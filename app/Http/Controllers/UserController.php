<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display listing of employees, permissions, and activity logs.
     */
    public function index(Request $request): View
    {
        $businessId = auth()->user()->business_id;

        $users = User::where('business_id', $businessId)
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->with('roles')
            ->latest()
            ->paginate(15);

        $roles = Role::whereIn('name', ['admin', 'manajer', 'kasir'])->get();
        $permissions = Permission::all();
        $branches = auth()->user()->business->branches()->where('is_active', true)->orderByDesc('is_primary')->get();

        return view('users.index', compact('users', 'roles', 'permissions', 'branches'));
    }

    /**
     * Store a newly created employee.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $contact = $data['contact'];

        // Keep the login identifier usable when the form only captures a phone number.
        $email = filter_var($contact, FILTER_VALIDATE_EMAIL)
            ? $contact
            : 'emp_'.Str::lower(Str::random(10)).'@pos.local';

        if (User::where('email', $email)->exists()) {
            return back()->withErrors(['contact' => 'Email ini sudah terdaftar.'])->withInput();
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $email,
            'password' => Hash::make($data['password'] ?? 'password123'),
            'pin' => $data['pin'] ?? null,
            'business_id' => auth()->user()->business_id,
            'branch_id' => $data['branch_id'] ?? null,
            'status' => User::STATUS_ACTIVE,
        ]);

        $user->assignRole($data['role']);

        return redirect()->route('users.index')->with('status', 'Karyawan baru berhasil ditambahkan.');
    }

    /**
     * Activate the specified user.
     */
    public function activate(User $user): RedirectResponse
    {
        $this->authorize('deactivate', $user);

        $user->update(['status' => User::STATUS_ACTIVE]);

        return redirect()->route('users.index')->with('status', 'Karyawan berhasil diaktifkan.');
    }

    /**
     * Update the specified user/employee.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $contact = $data['contact'];

        $email = filter_var($contact, FILTER_VALIDATE_EMAIL)
            ? $contact
            : $user->email;

        if (filter_var($contact, FILTER_VALIDATE_EMAIL)
            && User::where('email', $email)->where('id', '!=', $user->id)->exists()) {
            return back()->withErrors(['contact' => 'Email ini sudah digunakan akun lain.'])->withInput();
        }

        $payload = [
            'name' => $data['name'],
            'email' => $email,
            'branch_id' => $data['branch_id'] ?? null,
        ];

        if (! empty($data['pin'])) {
            $payload['pin'] = $data['pin'];
        }

        if (! empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        if ($user->id !== auth()->id() && array_key_exists('is_active', $data)) {
            $payload['status'] = $request->boolean('is_active')
                ? User::STATUS_ACTIVE
                : User::STATUS_INACTIVE;
        }

        $user->update($payload);

        // Never let an admin change their own role and lock themselves out.
        $roleName = $user->id === auth()->id()
            ? $user->getRoleNames()->first()
            : $data['role'];

        if ($roleName) {
            $user->syncRoles([$roleName]);
        }

        return redirect()->route('users.index')->with('status', 'Data karyawan berhasil diperbarui.');
    }

    /**
     * Deactivate the specified user.
     */
    public function deactivate(User $user): RedirectResponse
    {
        $this->authorize('deactivate', $user);

        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->withErrors(['error' => 'Anda tidak dapat menonaktifkan akun sendiri.']);
        }

        $user->update(['status' => User::STATUS_INACTIVE]);

        return redirect()->route('users.index')->with('status', 'Karyawan berhasil dinonaktifkan.');
    }
}
