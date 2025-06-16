<?php

namespace App\Livewire;

use App\Services\CartService;
use Livewire\Component;

class CartIcon extends Component
{
    public $totalQuantity = 0;

    protected $listeners = ['cart_updated' => 'refreshQuantity'];

    public function mount(CartService $cartService)
    {
        $this->totalQuantity = $cartService->getTotalQuantity();
    }

    public function refreshQuantity(CartService $cartService)
    {
        $this->totalQuantity = $cartService->getTotalQuantity();
    }

    public function render()
    {
        return view('livewire.cart-icon');
    }
}
