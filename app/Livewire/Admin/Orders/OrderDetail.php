<?php

namespace App\Livewire\Admin\Orders;

use App\Models\Order;
use Livewire\Component;

class OrderDetail extends Component
{
    public Order $order;

    public function mount(Order $order)
    {
        $this->order = $order->load('items.product', 'user');
    }

    // Example of how you might add status update logic in the future
    /*
    public function updateStatus($newStatus)
    {
        // Add authorization checks: Gate::authorize('update-order', $this->order);
        $this->order->status = $newStatus;
        $this->order->save();
        session()->flash('message', 'Order status updated successfully.');
        // Potentially dispatch an event: event(new OrderStatusUpdated($this->order));
    }
    */

    public function render()
    {
        return view('livewire.admin.orders.order-detail', [
            'orderLoaded' => $this->order // Pass the loaded order explicitly to the view
        ])->layout('layouts.admin');
    }
}
