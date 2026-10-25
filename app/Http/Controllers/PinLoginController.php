<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PinLoginController extends Controller
{
    public function show(): View
    {
        return view('pos.pin-login', [
            'businessCode' => old('business_code'),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'business_code' => ['required', 'string', 'max:255'],
            'pin' => ['required', 'string', 'min:4', 'max:6'],
        ]);

        $business = Business::where('slug', strtolower(trim($data['business_code'])))->first();

        if (! $business) {
            return back()
                ->withInput()
                ->withErrors(['business_code' => 'Kode usaha tidak ditemukan.']);
        }

        $pin = $data['pin'];
        $lookup = User::pinLookup($pin);

        // Fast path: single indexed lookup, then verify the slow hash.
        $user = User::where('business_id', $business->id)
            ->whereNotNull('pin')
            ->where('pin_lookup', $lookup)
            ->first();

        if ($user && Hash::check($pin, $user->pin)) {
            return $this->authenticate($request, $user);
        }

        // Legacy fallback for users created before pin_lookup existed.
        // Backfills the lookup so the next login uses the fast path.
        $legacyUsers = User::where('business_id', $business->id)
            ->whereNotNull('pin')
            ->whereNull('pin_lookup')
            ->get();

        foreach ($legacyUsers as $candidate) {
            if (Hash::check($pin, $candidate->pin)) {
                $candidate->pin_lookup = $lookup;
                $candidate->saveQuietly();

                return $this->authenticate($request, $candidate);
            }
        }

        return back()->withInput()->withErrors(['pin' => 'PIN yang Anda masukkan salah.']);
    }

    private function authenticate(Request $request, User $user): RedirectResponse
    {
        Auth::login($user);
        $request->session()->regenerate();

        if ($user->hasRole('kasir')) {
            return redirect()->route('pos.index');
        }

        return redirect()->route('dashboard');
    }
}
