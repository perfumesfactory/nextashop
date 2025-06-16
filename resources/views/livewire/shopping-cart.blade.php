<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-semibold text-gray-800 mb-8">Your Shopping Cart</h1>

    @if (session()->has('notify')) {{-- Assuming a general notification display, not specific to this component --}}
        <div class="mb-4 p-4 rounded-md {{ session('notify')['type'] == 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
            {{ session('notify')['message'] }}
        </div>
    @endif

    @if (empty($cartItems))
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <p class="mt-4 text-xl text-gray-600">Your cart is empty.</p>
            <a href="{{ route('products.index') }}" class="mt-6 inline-block bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition-colors">
                Continue Shopping
            </a>
        </div>
    @else
        <div class="bg-white shadow-lg rounded-lg overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($cartItems as $productId => $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-16 w-16">
                                        @if($item['image_path'] && Storage::disk('public')->exists($item['image_path']))
                                            <img class="h-16 w-16 rounded-md object-cover" src="{{ Storage::url($item['image_path']) }}" alt="{{ $item['name'] }}">
                                        @else
                                            <div class="h-16 w-16 rounded-md bg-gray-200 flex items-center justify-center">
                                                <span class="text-gray-500 text-xs">No Image</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $item['name'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">${{ number_format($item['price'], 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="number"
                                       min="1"
                                       wire:model.lazy="cartItems.{{ $productId }}.quantity"
                                       wire:change="updateQuantity({{ $productId }}, $event.target.value)"
                                       class="w-20 border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">${{ number_format($item['price'] * $item['quantity'], 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="removeItem({{ $productId }})" class="text-red-600 hover:text-red-800">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-8 flex flex-col md:flex-row justify-between items-start">
            <div class="mb-4 md:mb-0">
                <button wire:click="clearCart"
                        wire:confirm="Are you sure you want to clear your cart?"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-lg shadow-sm transition-colors">
                    Clear Cart
                </button>
            </div>
            <div class="w-full md:w-1/3 lg:w-1/4">
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Cart Summary</h2>
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-600">Subtotal:</span>
                        <span class="text-gray-800 font-semibold">${{ number_format($totalAmount, 2) }}</span>
                    </div>
                    {{-- Add other summary lines like Tax, Shipping if applicable later --}}
                    <div class="border-t border-gray-200 mt-2 pt-2 flex justify-between font-bold text-lg">
                        <span class="text-gray-800">Total:</span>
                        <span class="text-gray-800">${{ number_format($totalAmount, 2) }}</span>
                    </div>
                    <a href="{{ route('checkout.index') }}"
                       class="mt-6 block text-center w-full bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition-colors">
                        Proceed to Checkout
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
