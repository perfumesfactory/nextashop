@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white shadow-xl rounded-lg overflow-hidden">
        <div class="md:flex">
            <!-- Product Image -->
            <div class="md:w-1/2">
                @if($product->image_path && Storage::disk('public')->exists($product->image_path))
                    <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}" class="w-full h-auto md:h-full object-cover">
                @else
                    <div class="w-full h-96 bg-gray-200 flex items-center justify-center">
                        <span class="text-gray-500 text-xl">No Image Available</span>
                    </div>
                @endif
            </div>

            <!-- Product Details -->
            <div class="md:w-1/2 p-8">
                <h1 class="text-4xl font-bold text-gray-800 mb-4">{{ $product->name }}</h1>

                <p class="text-3xl font-semibold text-blue-600 mb-6">${{ number_format($product->price, 2) }}</p>

                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-gray-700 mb-2">Description</h2>
                    <p class="text-gray-600 leading-relaxed">
                        {!! nl2br(e($product->description)) !!}
                    </p>
                </div>

                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-gray-700 mb-2">Availability</h2>
                    @if($product->stock_quantity > 0)
                        <span class="text-green-600 font-semibold text-lg">In Stock ({{ $product->stock_quantity }} available)</span>
                    @else
                        <span class="text-red-600 font-semibold text-lg">Out of Stock</span>
                    @endif
                </div>

                @if($product->categories->isNotEmpty())
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold text-gray-700 mb-2">Categories</h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach($product->categories as $category)
                                <a href="{{ route('products.index', ['category' => $category->slug]) }}"
                                   class="bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-sm hover:bg-gray-300 transition-colors">
                                    {{ $category->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mt-8">
                    <livewire:add-to-cart-button :product="$product" :key="'show-'.$product->id" />
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products (Optional - Can be added later) -->
    {{-- <div class="mt-12">
        <h2 class="text-3xl font-semibold text-gray-700 mb-6">You might also like</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Placeholder for related products -->
        </div>
    </div> --}}
</div>
@endsection
