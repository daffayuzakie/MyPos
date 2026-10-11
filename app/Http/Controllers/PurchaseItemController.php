<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseItemRequest;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class PurchaseItemController extends Controller
{
    public function store(StorePurchaseItemRequest $request, Purchase $purchase): RedirectResponse
    {
        $this->authorizeBusiness($purchase);

        if ($purchase->status !== 'draft') {
            throw ValidationException::withMessages([
                'purchase' => 'Items can only be added to draft purchases.',
            ]);
        }

        $data = $request->validated();

        PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'product_id' => $data['product_id'],
            'quantity' => $data['quantity'],
            'unit_price' => $data['unit_price'],
            'subtotal' => $data['quantity'] * $data['unit_price'],
        ]);

        $this->updatePurchaseTotal($purchase);

        return redirect()->route('purchases.index')
            ->with('success', 'Item added to purchase.');
    }

    public function destroy(Purchase $purchase, PurchaseItem $item): RedirectResponse
    {
        $this->authorizeBusiness($purchase);

        if ($purchase->status !== 'draft') {
            throw ValidationException::withMessages([
                'purchase' => 'Items can only be removed from draft purchases.',
            ]);
        }

        $item->delete();

        $this->updatePurchaseTotal($purchase);

        return redirect()->route('purchases.index')
            ->with('success', 'Item removed from purchase.');
    }

    private function updatePurchaseTotal(Purchase $purchase): void
    {
        $total = $purchase->items()->sum('subtotal');
        $purchase->update(['total_amount' => $total]);
    }

    private function authorizeBusiness(Purchase $purchase): void
    {
        if ($purchase->business_id !== auth()->user()->business_id) {
            abort(403, 'Unauthorized access to purchase.');
        }
    }
}
