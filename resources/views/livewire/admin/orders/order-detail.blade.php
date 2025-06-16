<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-semibold text-gray-800">Order Details: #{{ $orderLoaded->id }}</h1>
        <a href="{{ route('admin.orders.index') }}" class="text-blue-500 hover:text-blue-700 font-medium">
            &larr; Back to Orders List
        </a>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column: Order Info & Customer/Shipping -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Order Information -->
            <div class="bg-white p-6 shadow-lg rounded-lg">
                <h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-2">Order Information</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Order ID:</p>
                        <p class="text-md font-medium text-gray-800">#{{ $orderLoaded->id }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Order Date:</p>
                        <p class="text-md font-medium text-gray-800">{{ $orderLoaded->created_at->format('M d, Y H:i A') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Order Status:</p>
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                            @if($orderLoaded->status == 'pending') bg-yellow-100 text-yellow-800
                            @elseif($orderLoaded->status == 'paid' || $orderLoaded->status == 'processing') bg-blue-100 text-blue-800
                            @elseif($orderLoaded->status == 'shipped' || $orderLoaded->status == 'delivered') bg-green-100 text-green-800
                            @elseif($orderLoaded->status == 'cancelled') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst($orderLoaded->status) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Amount:</p>
                        <p class="text-md font-medium text-gray-800">${{ number_format($orderLoaded->total_amount, 2) }}</p>
                    </div>
                </div>
                {{-- Future: Order Status Update Section --}}
                {{-- <div class="mt-6 pt-4 border-t">
                    <label for="order_status" class="block text-sm font-medium text-gray-700">Update Order Status:</label>
                    <select id="order_status" wire:change="updateStatus($event.target.value)" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="pending" @if($orderLoaded->status == 'pending') selected @endif>Pending</option>
                        <option value="processing" @if($orderLoaded->status == 'processing') selected @endif>Processing</option>
                        <option value="paid" @if($orderLoaded->status == 'paid') selected @endif>Paid</option>
                        <option value="shipped" @if($orderLoaded->status == 'shipped') selected @endif>Shipped</option>
                        <option value="delivered" @if($orderLoaded->status == 'delivered') selected @endif>Delivered</option>
                        <option value="cancelled" @if($orderLoaded->status == 'cancelled') selected @endif>Cancelled</option>
                    </select>
                </div> --}}
            </div>

            <!-- Customer & Shipping Information -->
            <div class="bg-white p-6 shadow-lg rounded-lg">
                <h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-2">Customer & Shipping</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Customer Name:</p>
                        <p class="text-md font-medium text-gray-800">{{ $orderLoaded->customer_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Customer Email:</p>
                        <p class="text-md font-medium text-gray-800">{{ $orderLoaded->customer_email }}</p>
                    </div>
                    @if($orderLoaded->user)
                    <div>
                        <p class="text-sm text-gray-500">Registered User:</p>
                        <p class="text-md font-medium text-gray-800">Yes (User ID: {{ $orderLoaded->user_id }})</p>
                    </div>
                    @else
                    <div>
                        <p class="text-sm text-gray-500">Registered User:</p>
                        <p class="text-md font-medium text-gray-800">No (Guest)</p>
                    </div>
                    @endif
                    <div class="sm:col-span-2">
                        <p class="text-sm text-gray-500">Shipping Address:</p>
                        <p class="text-md font-medium text-gray-800">
                            {{ $orderLoaded->shipping_address_line1 }}<br>
                            @if($orderLoaded->shipping_address_line2){{ $orderLoaded->shipping_address_line2 }}<br>@endif
                            {{ $orderLoaded->shipping_city }}, {{ $orderLoaded->shipping_state }} {{ $orderLoaded->shipping_postal_code }}<br>
                            {{ $orderLoaded->shipping_country }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Order Items -->
        <div class="lg:col-span-1 bg-white p-6 shadow-lg rounded-lg h-fit sticky top-8">
            <h2 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-2">Order Items</h2>
            @if($orderLoaded->items->isEmpty())
                <p class="text-gray-600">No items found for this order.</p>
            @else
                <div class="space-y-4">
                    @foreach($orderLoaded->items as $item)
                        <div class="flex items-start py-3 border-b border-gray-200 last:border-b-0">
                            @if($item->product && $item->product->image_path && Storage::disk('public')->exists($item->product->image_path))
                                <img src="{{ Storage::url($item->product->image_path) }}" alt="{{ $item->product_name }}" class="w-16 h-16 object-cover rounded-md mr-4">
                            @elseif($item->product_id && $item->product) {{-- Product exists but no image --}}
                                 <div class="w-16 h-16 bg-gray-200 rounded-md mr-4 flex items-center justify-center text-xs text-gray-500">No Image</div>
                            @else {{-- Product might be deleted or no product_id --}}
                                 <div class="w-16 h-16 bg-gray-100 rounded-md mr-4 flex items-center justify-center text-xs text-red-500 italic">Product N/A</div>
                            @endif
                            <div class="flex-grow">
                                <p class="text-md font-medium text-gray-800">
                                    @if($item->product_id && $item->product)
                                        <a href="{{ route('products.show', $item->product->slug) }}" class="hover:text-blue-600" target="_blank">
                                            {{ $item->product_name }}
                                        </a>
                                    @else
                                        {{ $item->product_name }}
                                    @endif
                                </p>
                                <p class="text-sm text-gray-600">Qty: {{ $item->quantity }}</p>
                                <p class="text-sm text-gray-600">Price: ${{ number_format($item->price_at_purchase, 2) }}</p>
                            </div>
                            <p class="text-md font-semibold text-gray-800">${{ number_format($item->price_at_purchase * $item->quantity, 2) }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6 pt-4 border-t border-gray-300">
                    <div class="flex justify-between items-center text-lg font-bold text-gray-800">
                        <span>Grand Total:</span>
                        <span>${{ number_format($orderLoaded->total_amount, 2) }}</span>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
