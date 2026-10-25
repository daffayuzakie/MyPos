<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center justify-between">
            {{ __('Sale Details') }}
            <a href="{{ route('sales.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
                {{ __('Back to Sales') }}
            </a>
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <!-- Sale Header -->
                <div class="p-6 border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Invoice Number') }}</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $sale->invoice_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Date') }}</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $sale->transaction_date->format('Y-m-d H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Cashier') }}</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $sale->user->name ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center gap-4">
                        @if ($sale->status === 'pending')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                {{ __('Pending') }}
                            </span>
                        @elseif ($sale->status === 'completed')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ __('Completed') }}
                            </span>
                        @elseif ($sale->status === 'cancelled')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                {{ __('Cancelled') }}
                            </span>
                        @elseif ($sale->status === 'refunded')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ __('Refunded') }}
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Sale Items -->
                <div class="border-b border-gray-200">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Items') }}</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Product') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Qty') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Unit Price') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Subtotal') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($sale->items as $item)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $item->product->name ?? 'N/A' }}</div>
                                                <div class="text-xs text-gray-500">SKU: {{ $item->product->sku ?? 'N/A' }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->quantity }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ number_format($item->unit_price, 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ number_format($item->subtotal, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Totals & Payment -->
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Totals') }}</h3>
                            <dl class="space-y-2">
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-600">{{ __('Subtotal') }}</dt>
                                    <dd class="text-sm font-medium text-gray-900">{{ number_format($sale->subtotal, 2) }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-600">{{ __('Discount') }}</dt>
                                    <dd class="text-sm font-medium text-gray-900">{{ number_format($sale->discount_amount, 2) }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-600">{{ __('Tax') }}</dt>
                                    <dd class="text-sm font-medium text-gray-900">{{ number_format($sale->tax_amount, 2) }}</dd>
                                </div>
                                <div class="flex justify-between border-t border-gray-200 pt-2">
                                    <dt class="text-lg font-bold text-gray-900">{{ __('Total') }}</dt>
                                    <dd class="text-lg font-bold text-gray-900">{{ number_format($sale->total_amount, 2) }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Payment') }}</h3>
                            @foreach ($sale->payments as $payment)
                                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                    <dl class="space-y-2">
                                        <div class="flex justify-between">
                                            <dt class="text-sm text-gray-600">{{ __('Method') }}</dt>
                                            <dd class="text-sm font-medium text-gray-900 capitalize">{{ $payment->method }}</dd>
                                        </div>
                                        <div class="flex justify-between">
                                            <dt class="text-sm text-gray-600">{{ __('Provider') }}</dt>
                                            <dd class="text-sm font-medium text-gray-900 capitalize">{{ $payment->provider }}</dd>
                                        </div>
                                        <div class="flex justify-between">
                                            <dt class="text-sm text-gray-600">{{ __('Amount') }}</dt>
                                            <dd class="text-sm font-medium text-gray-900">{{ number_format($payment->amount, 2) }}</dd>
                                        </div>
                                        <div class="flex justify-between">
                                            <dt class="text-sm text-gray-600">{{ __('Status') }}</dt>
                                            <dd class="text-sm font-medium">
                                                @if ($payment->status === 'paid')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ __('Paid') }}</span>
                                                @elseif ($payment->status === 'pending')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">{{ __('Pending') }}</span>
                                                @elseif ($payment->status === 'failed')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">{{ __('Failed') }}</span>
                                                @elseif ($payment->status === 'refunded')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">{{ __('Refunded') }}</span>
                                                @else
                                                    <span class="capitalize">{{ $payment->status }}</span>
                                                @endif
                                            </dd>
                                        </div>
                                        @if ($payment->paid_at)
                                            <div class="flex justify-between">
                                                <dt class="text-sm text-gray-600">{{ __('Paid At') }}</dt>
                                                <dd class="text-sm text-gray-900">{{ $payment->paid_at->format('Y-m-d H:i') }}</dd>
                                            </div>
                                        @endif
                                        @if ($payment->metadata && ($payment->metadata['received_cash'] || $payment->metadata['change']))
                                            <div class="flex justify-between">
                                                <dt class="text-sm text-gray-600">{{ __('Cash Received') }}</dt>
                                                <dd class="text-sm font-medium text-gray-900">{{ number_format($payment->metadata['received_cash'] ?? 0, 2) }}</dd>
                                            </div>
                                            <div class="flex justify-between">
                                                <dt class="text-sm text-gray-600">{{ __('Change') }}</dt>
                                                <dd class="text-sm font-medium text-green-600">{{ number_format($payment->metadata['change'] ?? 0, 2) }}</dd>
                                            </div>
                                        @endif
                                    </dl>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                @if ($sale->notes)
                    <div class="p-6 border-t border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Notes') }}</h3>
                        <p class="text-sm text-gray-600">{{ $sale->notes }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

