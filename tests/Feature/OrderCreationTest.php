<?php

namespace Tests\Feature;

use App\Livewire\AddToCartButton;
use App\Livewire\CheckoutPage;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;
use Tests\TestCase;

class OrderCreationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user); // Act as this user for all tests in this class

        // Seed categories if none exist, as products might need them
        if (Category::count() === 0) {
            Category::factory()->count(3)->create();
        }
    }

    /** @test */
    public function authenticated_user_can_place_an_order_successfully()
    {
        $product1 = Product::factory()->create(['stock_quantity' => 10, 'price' => 20.00]);
        $product2 = Product::factory()->create(['stock_quantity' => 5, 'price' => 10.00]);

        // Add items to cart
        Livewire::test(AddToCartButton::class, ['product' => $product1])->call('addToCart');
        Livewire::test(AddToCartButton::class, ['product' => $product2])->call('addToCart');

        // Proceed to Checkout and place order
        Livewire::test(CheckoutPage::class)
            ->set('customer_name', $this->user->name)
            ->set('customer_email', $this->user->email)
            ->set('shipping_address_line1', '123 Main St')
            ->set('shipping_city', 'Anytown')
            ->set('shipping_state', 'CA')
            ->set('shipping_postal_code', '90210')
            ->set('shipping_country', 'USA') // Ensure this matches default or is set
            ->call('placeOrder')
            ->assertRedirect(route('home'))
            ->assertSessionHas('notify', function ($value) {
                return $value['type'] === 'success' && str_contains($value['message'], 'Your order has been placed successfully!');
            });

        $this->assertDatabaseCount('orders', 1);
        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertEquals($this->user->id, $order->user_id);
        $this->assertEquals('123 Main St', $order->shipping_address_line1);
        $this->assertEquals(30.00, $order->total_amount); // (1*20 + 1*10)

        $this->assertDatabaseCount('order_items', 2);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product1->id,
            'quantity' => 1,
            'price_at_purchase' => 20.00
        ]);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product2->id,
            'quantity' => 1,
            'price_at_purchase' => 10.00
        ]);

        $this->assertEquals(9, $product1->refresh()->stock_quantity);
        $this->assertEquals(4, $product2->refresh()->stock_quantity);

        $cartService = app(CartService::class);
        $this->assertEquals(0, $cartService->getTotalQuantity());
    }

    /** @test */
    public function order_creation_fails_if_a_product_is_out_of_stock_during_checkout()
    {
        $product1 = Product::factory()->create(['stock_quantity' => 1, 'price' => 20.00]);

        Livewire::test(AddToCartButton::class, ['product' => $product1])->call('addToCart');

        // Product runs out of stock AFTER being added to cart
        $product1->update(['stock_quantity' => 0]);

        Livewire::test(CheckoutPage::class)
            ->set('customer_name', $this->user->name)
            ->set('customer_email', $this->user->email)
            ->set('shipping_address_line1', '123 Test St')
            ->set('shipping_city', 'Testville')
            ->set('shipping_state', 'TS')
            ->set('shipping_postal_code', '12345')
            ->set('shipping_country', 'USA')
            ->call('placeOrder')
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHas('notify', function ($value) use ($product1) {
                return $value['type'] === 'error' && str_contains(strtolower($value['message']), "not enough stock for product: " . strtolower($product1->name) );
            });

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertEquals(0, $product1->refresh()->stock_quantity);

        $cartService = app(CartService::class);
        $this->assertEquals(1, $cartService->getTotalQuantity()); // Cart should not be cleared
    }

    /** @test */
    public function validation_errors_on_checkout_page_prevent_order_creation()
    {
        // Add an item to make cart not empty, otherwise mount redirects
        $product = Product::factory()->create(['stock_quantity' => 1]);
        Livewire::test(AddToCartButton::class, ['product' => $product])->call('addToCart');

        Livewire::test(CheckoutPage::class)
            ->set('customer_name', '') // Name is required
            ->set('customer_email', $this->user->email)
            ->set('shipping_address_line1', '') // Required
            ->set('shipping_city', '') // Required
            ->set('shipping_state', '') // Required
            ->set('shipping_postal_code', '') // Required
            ->call('placeOrder')
            ->assertHasErrors([
                'customer_name' => 'required',
                'shipping_address_line1' => 'required',
                'shipping_city' => 'required',
                'shipping_state' => 'required',
                'shipping_postal_code' => 'required',
            ]);

        $this->assertDatabaseCount('orders', 0);
    }

    /** @test */
    public function redirect_to_products_if_cart_is_empty_when_accessing_checkout_page_via_get_request()
    {
        // Ensure cart is empty by forgetting session
        Session::forget('cart');

        $response = $this->get(route('checkout.index'));

        // Logic in CheckoutPage mount method redirects to products.index if cart is empty
        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('notify', function ($value) {
            return $value['type'] === 'error' && str_contains($value['message'], 'Your cart is empty.');
        });
    }

     /** @test */
    public function redirect_to_products_if_cart_is_empty_when_livewire_mounts()
    {
        Session::forget('cart'); // Ensure cart is empty

        Livewire::test(CheckoutPage::class)
            ->assertRedirect(route('products.index'))
            ->assertSessionHas('notify', function ($value) {
                 return $value['type'] === 'error' && str_contains($value['message'], 'Your cart is empty.');
            });
    }
}
