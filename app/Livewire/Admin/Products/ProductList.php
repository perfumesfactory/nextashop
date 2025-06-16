<?php

namespace App\Livewire\Admin\Products;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage; // Correct placement

class ProductList extends Component
{
    use WithPagination;

    public function deleteProduct($productId)
    {
        $product = Product::findOrFail($productId);
        // TODO: Add authorization check

        // Delete product image if it exists
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        session()->flash('message', 'Product deleted successfully.');
    }

    public function render()
    {
        return view('livewire.admin.products.product-list', [
            'products' => Product::latest()->paginate(10),
        ])->layout('layouts.admin'); // Assuming an admin layout exists
    }
}
