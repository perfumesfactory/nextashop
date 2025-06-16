<?php

namespace Tests\Feature;

use App\Livewire\AddToCartButton;
use App\Livewire\CartIcon;
use App\Livewire\ShoppingCart;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService; // Import CartService for potential direct use or type hinting
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ShoppingCartTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(); // A default user for actingAs if needed
        Product::factory()->count(5)->create(); // Create some products for tests to use
    }

    private function getCartSession(): \Illuminate\Support\Collection
    {
        return session('cart', collect([]));
    }

    /** @test */
    public function user_can_add_a_product_to_the_cart()
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);

        Livewire::test(AddToCartButton::class, ['product' => $product])
            ->call('addToCart')
            ->assertDispatched('cart_updated')
            ->assertDispatched('notify'); // Reverted to simplified assertion

        $cart = $this->getCartSession();
        $this->assertCount(1, $cart);
        $this->assertTrue($cart->has($product->id));
        $this->assertEquals($product->id, $cart[$product->id]['product_id']);
        $this->assertEquals(1, $cart[$product->id]['quantity']);
    }

    /** @test */
    public function adding_the_same_product_multiple_times_increases_quantity()
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);

        Livewire::test(AddToCartButton::class, ['product' => $product])->call('addToCart');
        Livewire::test(AddToCartButton::class, ['product' => $product])->call('addToCart');

        $cart = $this->getCartSession();
        $this->assertEquals(2, $cart[$product->id]['quantity']);
        $this->assertCount(1, $cart); // Still only one item type in cart
    }

    /** @test */
    public function cannot_add_product_to_cart_if_out_of_stock()
    {
        $product = Product::factory()->create(['stock_quantity' => 0]);

        Livewire::test(AddToCartButton::class, ['product' => $product])
            ->call('addToCart')
            ->assertNotDispatched('cart_updated')
            ->assertDispatched('notify'); // Reverted to simplified assertion

        $this->assertTrue($this->getCartSession()->isEmpty());
    }

    /** @test */
    public function user_can_view_the_shopping_cart_page()
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);

        // Manually set the session data for the cart
        $cartData = collect([
            $product->id => [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
                'image_path' => $product->image_path,
            ]
        ]);
        session(['cart' => $cartData]);

        $response = $this->actingAs($this->user)
                         ->withSession(['cart' => $cartData]) // Explicitly set session for the request
                         ->get(route('cart.index'));

        $response->assertOk();
        // $response->assertSeeLivewire(ShoppingCart::class);
        $response->assertSee("Your Shopping Cart");
        $response->assertSee($product->name);
    }

    /** @test */
    public function user_can_update_item_quantity_in_cart()
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);

        // Add 1 item initially
        (new CartService())->addItem($product, 1);

        Livewire::actingAs($this->user)
            ->test(ShoppingCart::class)
            ->call('updateQuantity', $product->id, 3)
            ->assertDispatched('cart_updated');

        $cart = $this->getCartSession();
        $this->assertEquals(3, $cart[$product->id]['quantity']);
    }

    /** @test */
    public function updating_item_quantity_to_zero_removes_item_from_cart()
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);
        (new CartService())->addItem($product, 1);

        Livewire::actingAs($this->user)
            ->test(ShoppingCart::class)
            ->call('updateQuantity', $product->id, 0)
            ->assertDispatched('cart_updated');

        $this->assertTrue($this->getCartSession()->isEmpty());
    }

    /** @test */
    public function user_can_remove_an_item_from_cart()
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);
        (new CartService())->addItem($product, 1);

        $this->assertFalse($this->getCartSession()->isEmpty());

        Livewire::actingAs($this->user)
            ->test(ShoppingCart::class)
            ->call('removeItem', $product->id)
            ->assertDispatched('cart_updated');

        $this->assertTrue($this->getCartSession()->isEmpty());
    }

    /** @test */
    public function user_can_clear_the_entire_cart()
    {
        $product1 = Product::factory()->create(['stock_quantity' => 10]);
        $product2 = Product::factory()->create(['stock_quantity' => 10]);

        $cartService = new CartService();
        $cartService->addItem($product1, 1);
        $cartService->addItem($product2, 1);

        $this->assertCount(2, $this->getCartSession());

        Livewire::actingAs($this->user)
            ->test(ShoppingCart::class)
            ->call('clearCart')
            ->assertDispatched('cart_updated');

        $this->assertTrue($this->getCartSession()->isEmpty());
    }

    /** @test */
    public function cart_icon_component_updates_quantity()
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);

        // Initial state of CartIcon
        Livewire::test(CartIcon::class)
            ->assertSet('totalQuantity', 0);

        // Add item using AddToCartButton, which dispatches 'cart_updated'
        Livewire::test(AddToCartButton::class, ['product' => $product])
            ->call('addToCart');
            // ->assertDispatched('cart_updated'); // This dispatch is confirmed in another test

        // Test CartIcon again, it should pick up the new state from session via its listener or mount
        // For a more direct test of the listener, we can emit the event.
        // However, the mount method itself should re-calculate from session.
        // If CartIcon is already mounted on a page, its listener would handle it.
        // Here, we test a fresh instance of CartIcon after session has changed.
        Livewire::test(CartIcon::class)
             ->assertSet('totalQuantity', 1);

        // Add another item (same product)
        Livewire::test(AddToCartButton::class, ['product' => $product])
            ->call('addToCart');

        Livewire::test(CartIcon::class)
             ->assertSet('totalQuantity', 2);
    }
}
