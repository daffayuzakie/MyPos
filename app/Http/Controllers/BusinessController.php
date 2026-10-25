<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateBusinessRequest;
use App\Models\Business;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class BusinessController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegistrationForm(): View
    {
        return view('auth.register-business');
    }

    /**
     * Handle business registration.
     */
    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email', 'unique:businesses,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'business_code' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
        ]);

        return DB::transaction(function () use ($request) {
            // Ensure admin role exists before transaction
            $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

            $customCode = $request->input('business_code');
            $slug = $customCode ? Str::slug($customCode) : Str::slug($request->input('business_name'));
            $originalSlug = $slug;
            $count = 1;

            // Handle slug uniqueness with retry logic for race conditions
            while (true) {
                if (! Business::where('slug', $slug)->exists()) {
                    break;
                }
                $slug = $originalSlug.'-'.$count++;
            }

            $business = Business::create([
                'name' => $request->input('business_name'),
                'slug' => $slug,
                'email' => $request->input('email'),
                'type' => $request->input('type', 'retail_store'),
            ]);

            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'business_id' => $business->id,
                'status' => 'active',
            ]);

            $user->assignRole($adminRole);

            Auth::login($user);

            return redirect()->route('dashboard');
        });
    }

    /**
     * Display business settings.
     */
    public function settings(): View
    {
        $this->authorize('view', auth()->user()->business);

        return view('business.settings', [
            'business' => auth()->user()->business,
        ]);
    }

    /**
     * Update business settings.
     */
    public function update(UpdateBusinessRequest $request): RedirectResponse
    {
        $business = auth()->user()->business;

        $this->authorize('update', $business);

        $data = $request->validated();
        unset($data['logo']);

        if ($request->hasFile('logo')) {
            if ($business->logo) {
                Storage::disk('public')->delete($business->logo);
            }

            $data['logo'] = $request->file('logo')->store('business-logos', 'public');
        }

        $business->update($data);

        return redirect()->route('business.settings')
            ->with('status', 'Business settings updated successfully.');
    }
}
