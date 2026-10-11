<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PinLoginController extends Controller
{
    public function show()
    {
        return view('pos.pin-login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'pin' => 'required|string',
        ]);

        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $businessId = auth()->user()->business_id;
        $users = User::where('business_id', $businessId)->whereNotNull('pin')->get();

        foreach ($users as $user) {
            if (Hash::check($request->pin, $user->pin)) {
                Auth::login($user);

                return redirect()->route('pos.index');
            }
        }

        return back()->withErrors(['pin' => 'Invalid PIN']);
    }
}
