<?php

namespace App\Livewire;

use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CheckoutPage extends Component
{
    // Customer Information
    public $customer_name = '';
    public $customer_email = '';

    // Shipping Information
    public $shipping_address_line1 = '';
    public $shipping_address_line2 = '';
    public $shipping_city = '';
    public $shipping_state = '';
    public $shipping_postal_code = '';
    public $shipping_country = 'United States'; // Default country

    // Cart Summary
    public $cartItems = [];
    public $totalAmount = 0;

    public function mount(CartService $cartService)
    {
        if ($cartService->getTotalQuantity() === 0) {
            session()->flash('notify', ['message' => 'Your cart is empty. Please add products before proceeding to checkout.', 'type' => 'error']);
            return redirect()->route('products.index');
        }

        $this->cartItems = $cartService->getCart()->toArray();
        $this->totalAmount = $cartService->getTotalAmount();

        if (Auth::check()) {
            $user = Auth::user();
            $this->customer_name = $user->name;
            $this->customer_email = $user->email;
            // Potentially load saved address if available
        }
    }

    protected function rules()
    {
        return [
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'shipping_address_line1' => 'required|string|max:255',
            'shipping_address_line2' => 'nullable|string|max:255',
            'shipping_city' => 'required|string|max:255',
            'shipping_state' => 'required|string|max:255',
            'shipping_postal_code' => 'required|string|max:10',
            'shipping_country' => 'required|string|max:255',
        ];
    }

    public function placeOrder(CartService $cartService)
    {
        $this->validate(); // Validate form input first

        $checkoutDataFromForm = [
             'customer_info' => [
                'name' => $this->customer_name,
                'email' => $this->customer_email,
            ],
            'shipping_info' => [
                'address_line1' => $this->shipping_address_line1,
                'address_line2' => $this->shipping_address_line2,
                'city' => $this->shipping_city,
                'state' => $this->shipping_state,
                'postal_code' => $this->shipping_postal_code,
                'country' => $this->shipping_country,
            ],
            'cart_items' => $cartService->getCart()->map(function ($item) {
                return [
                    'product_id' => $item['product_id'],
                    'name' => $item['name'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'image_path' => $item['image_path'] ?? null,
                    'subtotal' => $item['price'] * $item['quantity'],
                ];
            })->toArray(),
            'total_amount' => $cartService->getTotalAmount(),
            'user_id' => Auth::id(),
        ];

        session(['checkout_data' => $checkoutDataFromForm]);

        $checkoutData = session('checkout_data');

        if (!$checkoutData || empty($checkoutData['cart_items'])) {
            session()->flash('notify', ['message' => 'Your session has expired or cart is empty. Please try again.', 'type' => 'error']);
            return redirect()->route('cart.index');
        }

        DB::beginTransaction();

        try {
            $order = Order::create([
                'user_id' => $checkoutData['user_id'] ?? null,
                'customer_name' => $checkoutData['customer_info']['name'],
                'customer_email' => $checkoutData['customer_info']['email'],
                'shipping_address_line1' => $checkoutData['shipping_info']['address_line1'],
                'shipping_address_line2' => $checkoutData['shipping_info']['address_line2'] ?? null,
                'shipping_city' => $checkoutData['shipping_info']['city'],
                'shipping_state' => $checkoutData['shipping_info']['state'],
                'shipping_postal_code' => $checkoutData['shipping_info']['postal_code'],
                'shipping_country' => $checkoutData['shipping_info']['country'],
                'total_amount' => $checkoutData['total_amount'],
                'status' => 'pending',
            ]);

            foreach ($checkoutData['cart_items'] as $cartItem) {
                $product = Product::find($cartItem['product_id']);
                if ($product) {
                    if ($product->stock_quantity < $cartItem['quantity']) {
                        throw new \Exception("Not enough stock for product: " . $product->name);
                    }
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $cartItem['product_id'],
                        'product_name' => $cartItem['name'],
                        'quantity' => $cartItem['quantity'],
                        'price_at_purchase' => $cartItem['price'],
                    ]);
                    $product->decrement('stock_quantity', $cartItem['quantity']);
                } else {
                     OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => null,
                        'product_name' => $cartItem['name'] . " (Product Missing)",
                        'quantity' => $cartItem['quantity'],
                        'price_at_purchase' => $cartItem['price'],
                    ]);
                    // Log::warning("Product ID {$cartItem['product_id']} not found during order creation for order {$order->id}");
                }
            }

            DB::commit();

            $cartService->clearCart();
            $this->dispatch('cart_updated');
            session()->forget('checkout_data');
            session()->flash('notify', ['message' => 'Your order has been placed successfully! Order ID: ' . $order->id, 'type' => 'success']);
            return redirect()->route('home');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('notify', ['message' => 'There was an issue placing your order: ' . $e->getMessage(), 'type' => 'error']);
            return redirect()->route('checkout.index');
        }
    }

    public function render()
    {
        return view('livewire.checkout-page')
            ->layout('layouts.app');
    }
}
