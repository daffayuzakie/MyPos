<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BranchController extends Controller
{
    /**
     * Show the branch selection screen.
     */
    public function select(Request $request): View|RedirectResponse
    {
        $branches = $request->user()->business
            ->branches()
            ->where('is_active', true)
            ->orderByDesc('is_primary')
            ->orderBy('name')
            ->get();

        if ($branches->count() <= 1) {
            return redirect()->route('dashboard');
        }

        return view('auth.select-branch', [
            'branches' => $branches,
            'activeBranchId' => $request->session()->get('active_branch_id', $request->user()->branch_id),
        ]);
    }

    /**
     * Set the active branch for the session.
     */
    public function switch(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'branch_id' => ['required', 'integer'],
        ]);

        $branch = $request->user()->business
            ->branches()
            ->where('is_active', true)
            ->whereKey($data['branch_id'])
            ->first();

        if (! $branch) {
            return back()->withErrors(['branch_id' => 'Cabang tidak valid.']);
        }

        $request->session()->put('active_branch_id', $branch->id);

        if ($request->user()->hasRole('kasir')) {
            return redirect()->route('pos.index');
        }

        return redirect()->intended(route('dashboard'));
    }
}
