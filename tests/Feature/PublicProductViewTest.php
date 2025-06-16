<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User; // Though admin might not be directly used, good for consistency in setup
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicProductViewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed data that might be expected on all public pages or by layouts
        // For example, if the layout tries to load categories or something.
        // Or create a general admin user if any global scope/middleware expects one.
        User::factory()->create([ // Default user, not necessarily admin for these tests
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Seed some categories and products for general availability
        // Individual tests will create specific products/categories they need for assertions.
        if (Category::count() === 0) {
            Category::factory()->count(5)->create();
        }
        if (Product::count() === 0) {
            Product::factory()->count(5)->create()->each(function ($product) {
                $categories = Category::inRandomOrder()->limit(rand(1,3))->get();
                if ($categories->isNotEmpty()) {
                    $product->categories()->attach($categories->pluck('id')->toArray());
                }
            });
        }
    }

    /** @test */
    public function user_can_view_product_listing_page()
    {
        $product1 = Product::factory()->create(['name' => 'Awesome Product One']);
        $product2 = Product::factory()->create(['name' => 'Cool Product Two']);

        $response = $this->get(route('products.index'));

        $response->assertOk();
        $response->assertSee($product1->name);
        $response->assertSee($product2->name);
        $response->assertSee('Add to Cart'); // General check for cart buttons
    }

    /** @test */
    public function product_listing_page_is_paginated()
    {
        // Create more products than the default pagination (12 per page in ProductController)
        Product::factory()->count(15)->create();

        $response = $this->get(route('products.index'));
        $response->assertOk();

        // Check for pagination HTML structure
        // Check for a link to page 2, as we have 15 products and paginate by 12
        $response->assertSee('?page=2');

        $productsOnPage = $response->viewData('products');
        $this->assertCount(12, $productsOnPage->items()); // Assuming 12 items per page
    }

    /** @test */
    public function user_can_view_product_detail_page()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Detailed Product',
            'description' => 'Full description here',
            'price' => 49.99,
            'stock_quantity' => 10
        ]);
        $product->categories()->attach($category->id);

        $response = $this->get(route('products.show', $product->slug));

        $response->assertOk();
        $response->assertSee($product->name);
        $response->assertSee($product->description);
        $response->assertSee(number_format($product->price, 2)); // Check formatted price
        $response->assertSee($category->name);
        $response->assertSee('Add to Cart');
    }

    /** @test */
    public function product_detail_page_shows_stock_status()
    {
        $productInStock = Product::factory()->create(['stock_quantity' => 5, 'name' => 'In Stock Prod']);
        $productOutOfStock = Product::factory()->create(['stock_quantity' => 0, 'name' => 'Out Of Stock Prod']);

        // Test In Stock Product
        $responseInStock = $this->get(route('products.show', $productInStock->slug));
        $responseInStock->assertOk();
        $responseInStock->assertSee('In Stock');
        // Check that the "Add to Cart" button is present (not disabled based on text only)
        $responseInStock->assertSeeText('Add to Cart');
        $responseInStock->assertDontSee('Out of Stock');

        // Test Out of Stock Product
        $responseOutOfStock = $this->get(route('products.show', $productOutOfStock->slug));
        $responseOutOfStock->assertOk();
        $responseOutOfStock->assertSee('Out of Stock');
        // Check that the "Add to Cart" button might be different or absent (here, we check for the specific "Out of Stock" button text from AddToCartButton)
        $responseOutOfStock->assertSeeText('Out of Stock'); // This is the text on the disabled button
        $responseOutOfStock->assertDontSee('>In Stock<'); // Ensure "In Stock" text is not present
    }

    /** @test */
    public function filtering_products_by_category_on_listing_page()
    {
        $catA = Category::factory()->create(['name' => 'Category Alpha', 'slug' => 'category-alpha']);
        $catB = Category::factory()->create(['name' => 'Category Beta', 'slug' => 'category-beta']);

        $productA = Product::factory()->create(['name' => 'Product Alpha']);
        $productA->categories()->attach($catA->id);

        $productB = Product::factory()->create(['name' => 'Product Beta']);
        $productB->categories()->attach($catB->id);

        // Test filtering by CatA
        $responseCatA = $this->get(route('products.index', ['category' => $catA->slug]));
        $responseCatA->assertOk();
        $responseCatA->assertSee($productA->name);
        $responseCatA->assertDontSee($productB->name);
        $responseCatA->assertSee($catA->name); // Check if category name is displayed as active filter

        // Test filtering by CatB
        $responseCatB = $this->get(route('products.index', ['category' => $catB->slug]));
        $responseCatB->assertOk();
        $responseCatB->assertDontSee($productA->name);
        $responseCatB->assertSee($productB->name);
        $responseCatB->assertSee($catB->name); // Check if category name is displayed as active filter
    }

    /** @test */
    public function viewing_non_existent_product_slug_returns_404()
    {
        $response = $this->get('/products/this-slug-does-not-exist');
        $response->assertNotFound();
    }
}
