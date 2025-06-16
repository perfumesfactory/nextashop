<?php

namespace App\Livewire;

use App\Services\CartService;
use Livewire\Component;

class ShoppingCart extends Component
{
    public $cartItems = [];
    public $totalAmount = 0;

    public function mount(CartService $cartService)
    {
        $this->loadCartData($cartService);
    }

    public function loadCartData(CartService $cartService)
    {
        $this->cartItems = $cartService->getCart()->toArray();
        $this->totalAmount = $cartService->getTotalAmount();
    }

    public function updateQuantity($productId, $quantity, CartService $cartService)
    {
        // Ensure quantity is at least 1, or handle as removal if 0 or less
        $newQuantity = max(0, (int)$quantity);

        if ($newQuantity == 0) {
            $cartService->removeItem($productId);
        } else {
            $cartService->updateItemQuantity($productId, $newQuantity);
        }

        $this->loadCartData($cartService);
        $this->dispatch('cart_updated'); // For CartIcon
    }

    public function removeItem($productId, CartService $cartService)
    {
        $cartService->removeItem($productId);
        $this->loadCartData($cartService);
        $this->dispatch('cart_updated'); // For CartIcon
        $this->dispatch('notify', ['message' => 'Item removed from cart.', 'type' => 'success']);
    }

    public function clearCart(CartService $cartService)
    {
        $cartService->clearCart();
        $this->loadCartData($cartService);
        $this->dispatch('cart_updated'); // For CartIcon
        $this->dispatch('notify', ['message' => 'Cart cleared successfully.', 'type' => 'success']);
    }

    public function render()
    {
        return view('livewire.shopping-cart')
            ->layout('layouts.app');
    }
}
