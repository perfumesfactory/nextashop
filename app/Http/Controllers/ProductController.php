<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $categories = Category::orderBy('name')->get();
        $productsQuery = Product::with('categories')->latest();

        if ($request->has('category')) {
            $categorySlug = $request->input('category');
            $category = Category::where('slug', $categorySlug)->firstOrFail();
            $productsQuery->whereHas('categories', function ($query) use ($category) {
                $query->where('categories.id', $category->id);
            });
        }

        $products = $productsQuery->paginate(12);

        return view('products.index', compact('products', 'categories'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load('categories');
        return view('products.show', compact('product'));
    }
}
