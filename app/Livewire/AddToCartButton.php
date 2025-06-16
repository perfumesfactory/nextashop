<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\CartService;
use Livewire\Component;

class AddToCartButton extends Component
{
    public Product $product;
    public $quantity = 1; // Default quantity to add

    public function mount(Product $product)
    {
        $this->product = $product;
    }

    public function addToCart(CartService $cartService)
    {
        if ($this->product->stock_quantity < $this->quantity && $this->product->stock_quantity > 0) {
            // If desired quantity is more than stock, but stock is available, add what's in stock
            // Or, you might want to prevent adding altogether if exact quantity isn't available.
            // For now, let's assume we add what's available or prevent if trying to add more than stock.
             $this->dispatch('notify', ['message' => 'Not enough stock to add ' . $this->quantity . ' items. Only ' . $this->product->stock_quantity . ' available.', 'type' => 'error']);
            return;
        }

        if ($this->product->stock_quantity <= 0) {
            $this->dispatch('notify', ['message' => 'This product is out of stock.', 'type' => 'error']);
            return;
        }

        $cartService->addItem($this->product, $this->quantity);
        $this->dispatch('cart_updated'); // For CartIcon
        $this->dispatch('notify', ['message' => '"' . $this->product->name . '" added to cart!', 'type' => 'success']);
    }

    public function render()
    {
        return view('livewire.add-to-cart-button');
    }
}
