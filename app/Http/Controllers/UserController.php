<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact' => 'required|string|max:255', // email or phone
            'role' => 'required|string|in:admin,kasir,manajer',
            'branch_id' => 'nullable|integer|exists:branches,id',
            'pin' => 'nullable|string|min:4|max:6',
            'password' => 'nullable|string|min:6',
        ]);

        $businessId = auth()->user()->business_id;
        $contact = $request->input('contact');

        // Determine if contact is email or phone
        $email = filter_var($contact, FILTER_VALIDATE_EMAIL) ? $contact : 'emp_'.time().'@pos.local';

        // Check unique email if actual email provided
        if (filter_var($contact, FILTER_VALIDATE_EMAIL) && User::where('email', $email)->exists()) {
            return back()->withErrors(['contact' => 'Email ini sudah terdaftar.'])->withInput();
        }

        $roleName = $request->input('role');
        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

        $branchId = $request->input('branch_id');

        if ($branchId && ! auth()->user()->business->branches()->whereKey($branchId)->exists()) {
            return back()->withErrors(['branch_id' => 'Cabang tidak valid.'])->withInput();
        }

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $email,
            'password' => Hash::make($request->input('password') ?? 'password123'),
            'pin' => $request->filled('pin') ? $request->input('pin') : null,
            'business_id' => $businessId,
            'branch_id' => $branchId,
            'status' => 'active',
        ]);

        $user->assignRole($role);

        return redirect()->route('users.index')->with('status', 'Karyawan baru berhasil ditambahkan.');
    }

    /**
     * Activate the specified user.
     */
    public function activate(User $user): RedirectResponse
    {
        if ($user->business_id !== auth()->user()->business_id) {
            abort(403);
        }

        $user->update(['status' => 'active']);

        return redirect()->route('users.index')->with('status', 'Karyawan berhasil diaktifkan.');
    }

    /**
     * Update the specified user/employee.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        if ($user->business_id !== auth()->user()->business_id) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'contact' => 'required|string|max:255',
            'role' => 'required|string|in:admin,kasir,manajer',
            'branch_id' => 'nullable|integer',
            'pin' => 'nullable|string|min:4|max:6',
            'password' => 'nullable|string|min:6',
        ]);

        $contact = $request->input('contact');
        $email = filter_var($contact, FILTER_VALIDATE_EMAIL) ? $contact : $user->email;

        if (filter_var($contact, FILTER_VALIDATE_EMAIL)) {
            $exists = User::where('email', $email)->where('id', '!=', $user->id)->exists();
            if ($exists) {
                return back()->withErrors(['contact' => 'Email ini sudah digunakan akun lain.'])->withInput();
            }
        }

        $data = [
            'name' => $request->input('name'),
            'email' => $email,
            'branch_id' => $request->input('branch_id') ?: null,
        ];

        if ($request->filled('pin')) {
            $data['pin'] = $request->input('pin');
        }
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
        }

        if ($request->has('is_active') && $user->id !== auth()->id()) {
            $data['status'] = $request->boolean('is_active') ? 'active' : 'inactive';
        }

        $user->update($data);

        $roleName = $request->input('role');
        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        $user->syncRoles([$role]);

        return redirect()->route('users.index')->with('status', 'Data karyawan berhasil diperbarui.');
    }

    /**
     * Deactivate the specified user.
     */
    public function deactivate(User $user): RedirectResponse
    {
        if ($user->business_id !== auth()->user()->business_id) {
            abort(403);
        }

        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->withErrors(['error' => 'Anda tidak dapat menonaktifkan akun sendiri.']);
        }

        $user->update(['status' => 'inactive']);

        return redirect()->route('users.index')->with('status', 'Karyawan berhasil dinonaktifkan.');
    }
}
