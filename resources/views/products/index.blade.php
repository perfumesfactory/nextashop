@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8 text-center">
        <h1 class="text-4xl font-bold text-gray-800">Our Products</h1>
        <p class="text-lg text-gray-600">Browse our collection of high-quality products.</p>
    </div>

    <div class="flex flex-col md:flex-row">
        <!-- Sidebar for Categories -->
        <aside class="w-full md:w-1/4 lg:w-1/5 p-4">
            <h2 class="text-2xl font-semibold mb-4 text-gray-700">Categories</h2>
            <ul class="space-y-2">
                <li>
                    <a href="{{ route('products.index') }}"
                       class="block px-4 py-2 rounded-md {{ !request()->query('category') ? 'bg-blue-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }} shadow-sm">
                        All Categories
                    </a>
                </li>
                @foreach($categories as $category)
                    <li>
                        <a href="{{ route('products.index', ['category' => $category->slug]) }}"
                           class="block px-4 py-2 rounded-md {{ request()->query('category') == $category->slug ? 'bg-blue-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }} shadow-sm">
                            {{ $category->name }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </aside>

        <!-- Product Grid -->
        <main class="w-full md:w-3/4 lg:w-4/5 p-4">
            @if($products->isEmpty())
                <div class="text-center text-gray-500 py-10">
                    <p class="text-2xl">No products found.</p>
                    @if(request()->query('category'))
                        <p class="mt-2">Try removing the category filter or selecting a different one.</p>
                    @endif
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($products as $product)
                        <div class="bg-white rounded-lg shadow-lg overflow-hidden transform hover:scale-105 transition-transform duration-300 ease-in-out flex flex-col">
                            <a href="{{ route('products.show', $product->slug) }}">
                                @if($product->image_path && Storage::disk('public')->exists($product->image_path))
                                    <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}" class="w-full h-56 object-cover">
                                @else
                                    <div class="w-full h-56 bg-gray-200 flex items-center justify-center">
                                        <span class="text-gray-500">No Image</span>
                                    </div>
                                @endif
                            </a>
                            <div class="p-6 flex-grow flex flex-col">
                                <h3 class="text-xl font-semibold text-gray-800 mb-2">
                                    <a href="{{ route('products.show', $product->slug) }}" class="hover:text-blue-600">{{ $product->name }}</a>
                                </h3>
                                <p class="text-2xl font-bold text-blue-600 mb-3">${{ number_format($product->price, 2) }}</p>
                                <div class="mt-auto">
                                    <livewire:add-to-cart-button :product="$product" :key="'index-'.$product->id" />
                                    <!-- Stock Info (Optional) -->
                                    {{-- <span class="text-sm {{ $product->stock_quantity > 0 ? 'text-green-500' : 'text-red-500' }}">
                                        {{ $product->stock_quantity > 0 ? 'In Stock' : 'Out of Stock' }}
                                    </span> --}}
                                </div>
                                @if($product->categories->isNotEmpty())
                                <div class="mt-3 text-xs text-gray-500">
                                    Categories: {{ $product->categories->pluck('name')->implode(', ') }}
                                </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-12">
                    {{ $products->appends(request()->query())->links() }}
                </div>
            @endif
        </main>
    </div>
</div>
@endsection
