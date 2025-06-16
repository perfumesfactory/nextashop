@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 text-center">
    <div class="bg-white shadow-lg rounded-lg p-10">
        <h1 class="text-5xl font-bold text-gray-800 mb-6">
            Welcome to {{ config('app.name', 'Laravel') }}!
        </h1>
        <p class="text-xl text-gray-600 mb-8">
            Your one-stop shop for amazing products. Browse our collection and find what you need.
        </p>
        <div>
            <a href="{{ route('products.index') }}"
               class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg text-lg shadow-md hover:shadow-lg transition-all duration-300 ease-in-out">
                Explore Products
            </a>
        </div>
    </div>

    <div class="mt-12">
        <h2 class="text-3xl font-semibold text-gray-700 mb-6">Why Shop With Us?</h2>
        <div class="grid md:grid-cols-3 gap-8 text-left">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Quality Products</h3>
                <p class="text-gray-600">We offer only the best products, curated for quality and reliability.</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Great Prices</h3>
                <p class="text-gray-600">Find competitive prices on all our items, ensuring you get the best value.</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Fast Shipping</h3>
                <p class="text-gray-600">Get your orders delivered quickly and efficiently to your doorstep.</p>
            </div>
        </div>
    </div>
</div>
@endsection
