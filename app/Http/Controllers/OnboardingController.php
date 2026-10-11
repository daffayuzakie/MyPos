<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    /**
     * Ordered onboarding steps (number => label).
     */
    public const STEPS = [
        1 => 'Profil Usaha',
        2 => 'Alamat & Cabang',
        3 => 'Pajak & Pembayaran',
        4 => 'Produk Pertama',
    ];

    /**
     * Display the current onboarding step.
     */
    public function show(Request $request): View|RedirectResponse
    {
        $business = $request->user()->business;

        if ($business->onboarded_at) {
            return redirect()->route('pos.index');
        }

        $step = (int) $request->session()->get('onboarding.step', 1);
        $step = min(max($step, 1), 4);

        return view('onboarding.index', [
            'business' => $business,
            'branch' => $business->primaryBranch,
            'step' => $step,
            'steps' => self::STEPS,
            'productCount' => Product::where('business_id', $business->id)->count(),
        ]);
    }

    /**
     * Store the business profile (step 1).
     */
    public function storeBusiness(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:100'],
            'timezone' => ['required', 'string', 'max:64'],
        ]);

        $request->user()->business->update($data);

        $request->session()->put('onboarding.step', 2);

        return redirect()->route('onboarding.show');
    }

    /**
     * Store the first branch (step 2).
     */
    public function storeBranch(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
        ]);

        $business = $request->user()->business;
        $branch = $business->primaryBranch;

        if ($branch) {
            $branch->update($data);
        } else {
            $branch = $business->branches()->create($data + [
                'is_primary' => true,
                'is_active' => true,
            ]);

            $business->users()->whereNull('branch_id')->update(['branch_id' => $branch->id]);
        }

        $request->session()->put('onboarding.step', 3);

        return redirect()->route('onboarding.show');
    }

    /**
     * Store tax and payment preferences (step 3).
     */
    public function storePreferences(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tax_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'payment_methods' => ['nullable', 'array'],
            'payment_methods.*' => ['string', 'in:cash,qris,debit,credit,ewallet,transfer'],
        ]);

        $request->user()->business->update([
            'tax_percentage' => $data['tax_percentage'],
            'payment_methods' => $data['payment_methods'] ?? [],
        ]);

        $request->session()->put('onboarding.step', 4);

        return redirect()->route('onboarding.show');
    }

    /**
     * Add the first product (step 4).
     */
    public function storeProduct(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'unit_name' => ['nullable', 'string', 'max:50'],
        ]);

        $business = $request->user()->business;
        $businessId = $business->id;

        DB::transaction(function () use ($data, $businessId, $request) {
            $unitName = $data['unit_name'] ?: 'Pcs';

            $unit = Unit::firstOrCreate(
                ['business_id' => $businessId, 'name' => $unitName],
                ['code' => Str::slug($unitName) ?: 'pcs', 'is_active' => true]
            );

            $category = Category::firstOrCreate(
                ['business_id' => $businessId, 'name' => 'Umum'],
                ['is_active' => true]
            );

            $product = Product::create([
                'business_id' => $businessId,
                'category_id' => $category->id,
                'unit_id' => $unit->id,
                'sku' => 'SKU-'.strtoupper(Str::random(6)),
                'name' => $data['name'],
                'purchase_price' => round((float) $data['selling_price'] * 0.7, 2),
                'selling_price' => $data['selling_price'],
                'is_active' => true,
            ]);

            $initialStock = (int) ($data['stock'] ?? 0);

            if ($initialStock > 0) {
                app(StockService::class)->performStockIn(
                    $product->id,
                    $initialStock,
                    $businessId,
                    $request->user()->id,
                    'Initial Stock',
                    'Stok awal produk'
                );
            }
        });

        return redirect()->route('onboarding.show')->with('status', 'Produk berhasil ditambahkan.');
    }

    /**
     * Finish onboarding and enter the POS.
     */
    public function complete(Request $request): RedirectResponse
    {
        $request->user()->business->forceFill(['onboarded_at' => now()])->save();

        $request->session()->forget('onboarding.step');

        return redirect()->route('pos.index')
            ->with('status', 'Selamat datang! Usaha Anda siap digunakan.');
    }
}
