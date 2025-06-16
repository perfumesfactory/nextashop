<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-semibold text-gray-800 mb-8 text-center">Checkout</h1>

    @if (session()->has('notify'))
        <div class="mb-6 p-4 rounded-md {{ session('notify.type') === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
            {{ session('notify.message') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Shipping Information Form -->
        <div class="md:col-span-2 bg-white p-8 rounded-lg shadow-xl">
            <h2 class="text-2xl font-semibold text-gray-700 mb-6">Shipping Information</h2>
            <form wire:submit.prevent="placeOrder">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="customer_name" class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" id="customer_name" wire:model.defer="customer_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('customer_name') border-red-500 @enderror">
                        @error('customer_name') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="customer_email" class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input type="email" id="customer_email" wire:model.defer="customer_email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('customer_email') border-red-500 @enderror">
                        @error('customer_email') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-6">
                    <label for="shipping_address_line1" class="block text-sm font-medium text-gray-700">Address Line 1</label>
                    <input type="text" id="shipping_address_line1" wire:model.defer="shipping_address_line1" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('shipping_address_line1') border-red-500 @enderror">
                    @error('shipping_address_line1') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="mt-6">
                    <label for="shipping_address_line2" class="block text-sm font-medium text-gray-700">Address Line 2 <span class="text-xs text-gray-500">(Optional)</span></label>
                    <input type="text" id="shipping_address_line2" wire:model.defer="shipping_address_line2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-6">
                    <div>
                        <label for="shipping_city" class="block text-sm font-medium text-gray-700">City</label>
                        <input type="text" id="shipping_city" wire:model.defer="shipping_city" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('shipping_city') border-red-500 @enderror">
                        @error('shipping_city') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="shipping_state" class="block text-sm font-medium text-gray-700">State / Province</label>
                        <input type="text" id="shipping_state" wire:model.defer="shipping_state" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('shipping_state') border-red-500 @enderror">
                        @error('shipping_state') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-6">
                    <div>
                        <label for="shipping_postal_code" class="block text-sm font-medium text-gray-700">Postal Code</label>
                        <input type="text" id="shipping_postal_code" wire:model.defer="shipping_postal_code" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('shipping_postal_code') border-red-500 @enderror">
                        @error('shipping_postal_code') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="shipping_country" class="block text-sm font-medium text-gray-700">Country</label>
                        <input type="text" id="shipping_country" wire:model.defer="shipping_country" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('shipping_country') border-red-500 @enderror">
                        @error('shipping_country') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-8">
                    <button type="submit"
                            class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-6 rounded-lg text-lg shadow-lg transition-transform transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-opacity-50">
                        Continue to Order Summary
                    </button>
                </div>
            </form>
        </div>

        <!-- Order Summary -->
        <div class="md:col-span-1 bg-gray-50 p-8 rounded-lg shadow-xl h-fit sticky top-8">
            <h2 class="text-2xl font-semibold text-gray-700 mb-6">Order Summary</h2>
            @if(empty($cartItems))
                <p class="text-gray-600">Your cart is currently empty.</p>
            @else
                <div class="space-y-4">
                    @foreach($cartItems as $item)
                        <div class="flex items-center justify-between pb-2 border-b border-gray-200 last:border-b-0">
                            <div class="flex items-center">
                                @if($item['image_path'] && Storage::disk('public')->exists($item['image_path']))
                                    <img src="{{ Storage::url($item['image_path']) }}" alt="{{ $item['name'] }}" class="w-12 h-12 object-cover rounded-md mr-3">
                                @else
                                     <div class="w-12 h-12 bg-gray-200 rounded-md mr-3 flex items-center justify-center text-xs text-gray-500">No Image</div>
                                @endif
                                <div>
                                    <p class="text-sm font-medium text-gray-800">{{ $item['name'] }}</p>
                                    <p class="text-xs text-gray-500">Qty: {{ $item['quantity'] }}</p>
                                </div>
                            </div>
                            <p class="text-sm font-semibold text-gray-800">${{ number_format($item['price'] * $item['quantity'], 2) }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6 pt-4 border-t border-gray-300">
                    <div class="flex justify-between items-center text-xl font-bold text-gray-800">
                        <span>Total:</span>
                        <span>${{ number_format($totalAmount, 2) }}</span>
                    </div>
                </div>
            @endif
            <div class="mt-6">
                 <a href="{{ route('cart.index') }}" class="text-blue-500 hover:text-blue-700 text-sm font-medium">
                    &larr; Return to Cart
                </a>
            </div>
        </div>
    </div>
</div>
