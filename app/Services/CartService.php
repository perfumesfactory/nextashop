<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class CartService
{
    protected Collection $cart;

    public function __construct()
    {
        $this->cart = session('cart', collect([]));
    }

    public function getCart(): Collection
    {
        return $this->cart->map(function ($item) {
            // Ensure product details are up-to-date if they were stored minimally
            // For this example, we assume product details are stored directly in the cart item
            // Or, you might want to re-fetch product from DB if only ID was stored.
            // For simplicity, let's assume 'name' and 'price' are stored.
            $item['subtotal'] = $item['price'] * $item['quantity'];
            return $item;
        });
    }

    public function addItem(Product $product, int $quantity = 1): void
    {
        if ($quantity <= 0) {
            return;
        }

        if ($this->cart->has($product->id)) {
            $this->cart[$product->id]['quantity'] += $quantity;
        } else {
            $this->cart[$product->id] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $quantity,
                'image_path' => $product->image_path, // Store image path
            ];
        }
        $this->saveCart();
    }

    public function updateItemQuantity(int $productId, int $quantity): void
    {
        if (!$this->cart->has($productId)) {
            return;
        }

        if ($quantity <= 0) {
            $this->removeItem($productId);
        } else {
            $this->cart[$productId]['quantity'] = $quantity;
            $this->saveCart();
        }
    }

    public function removeItem(int $productId): void
    {
        $this->cart->forget($productId);
        $this->saveCart();
    }

    public function clearCart(): void
    {
        $this->cart = collect([]);
        $this->saveCart();
    }

    public function getTotalQuantity(): int
    {
        return $this->cart->sum('quantity');
    }

    public function getTotalAmount(): float
    {
        return $this->cart->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });
    }

    protected function saveCart(): void
    {
        session(['cart' => $this->cart]);
    }

    // Optional: Method to get a specific item if needed elsewhere
    public function getItem(int $productId)
    {
        return $this->cart->get($productId);
    }
}
