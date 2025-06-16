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
        // Eager load user relationship for efficiency if you display user details like name/email
        $orders = Order::with('user')->latest()->paginate(10);

        return view('livewire.admin.orders.order-list', [
            'orders' => $orders,
        ])->layout('layouts.admin');
    }
}
