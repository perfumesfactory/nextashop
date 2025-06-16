<?php

namespace App\Livewire\Admin\Orders;

use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;

class OrderList extends Component
{
    use WithPagination;

    public function render()
    {
        // Eager load user relationship for efficiency, and add secondary sort for deterministic pagination
        $orders = Order::with('user')->latest()->orderBy('id', 'desc')->paginate(10);

        return view('livewire.admin.orders.order-list', [
            'orders' => $orders,
        ])->layout('layouts.admin');
    }
}
