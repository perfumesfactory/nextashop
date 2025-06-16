<?php

namespace App\Livewire\Admin\Products;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class ProductList extends Component
{
    use WithPagination;

    public function deleteProduct($productId)
    {
        $product = Product::findOrFail($productId);
        // TODO: Add authorization check
        // TODO: Delete product image if it exists
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
