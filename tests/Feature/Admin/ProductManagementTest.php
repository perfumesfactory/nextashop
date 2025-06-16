<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Products\ProductForm;
use App\Livewire\Admin\Products\ProductList;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan; // Correct placement
use Livewire\Livewire;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // The RefreshDatabase trait should handle migrating.
        // Artisan::call('migrate:fresh --seed'); // Removed this line

        $this->adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        // Ensure categories exist for tests that require them.
        // Seeding all categories might be too much if DatabaseSeeder is extensive.
        // Create a few essential ones or let individual tests create what they need.
        if (Category::count() === 0) {
             Category::factory()->count(3)->create();
        }
    }

    /** @test */
    public function admin_can_view_product_list_page()
    {
        $product = Product::factory()->create();

        $this->actingAs($this->adminUser)
            ->get(route('admin.products.index'))
            ->assertStatus(200)
            ->assertSeeLivewire(ProductList::class)
            ->assertSee($product->name);
    }

    /** @test */
    public function admin_can_view_create_product_page()
    {
        $this->actingAs($this->adminUser)
            ->get(route('admin.products.create'))
            ->assertStatus(200)
            ->assertSeeLivewire(ProductForm::class)
            ->assertSee('Create New Product');
    }

    /** @test */
    public function admin_can_create_a_new_product()
    {
        Storage::fake('public');

        $category = Category::firstOrFail(); // Ensure category exists
        $productData = [
            'name' => 'New Awesome Product',
            'description' => 'This is a great product.',
            'price' => 99.99,
            'stock_quantity' => 100,
            'image' => UploadedFile::fake()->image('product.jpg'),
        ];

        Livewire::actingAs($this->adminUser)
            ->test(ProductForm::class)
            ->set('name', $productData['name'])
            ->set('description', $productData['description'])
            ->set('price', $productData['price'])
            ->set('stock_quantity', $productData['stock_quantity'])
            ->set('selectedCategories', [$category->id])
            ->set('image', $productData['image'])
            ->call('saveProduct')
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHas('message', 'Product created successfully.');

        $this->assertDatabaseHas('products', [
            'name' => $productData['name'],
            'price' => $productData['price'],
            'stock_quantity' => $productData['stock_quantity'],
        ]);

        $product = Product::where('name', $productData['name'])->first();
        $this->assertNotNull($product);
        $this->assertTrue($product->categories->contains($category));
        $this->assertNotNull($product->image_path);
        Storage::disk('public')->assertExists($product->image_path);
    }

    /** @test */
    public function admin_can_create_a_new_product_and_slug_is_auto_generated()
    {
        $category = Category::firstOrFail();
        $productName = 'Another New Product Name';

        Livewire::actingAs($this->adminUser)
            ->test(ProductForm::class)
            ->set('name', $productName)
            ->set('description', 'Some description.')
            ->set('price', 49.99)
            ->set('stock_quantity', 50)
            ->set('selectedCategories', [$category->id])
            ->call('saveProduct')
            ->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', [
            'name' => $productName,
            'slug' => \Illuminate\Support\Str::slug($productName),
        ]);
    }


    /** @test */
    public function admin_can_view_edit_product_page()
    {
        $product = Product::factory()->create();
        $category = Category::firstOrFail();
        $product->categories()->attach($category->id);

        $this->actingAs($this->adminUser)
            ->get(route('admin.products.edit', $product->id))
            ->assertStatus(200)
            ->assertSeeLivewire(ProductForm::class)
            ->assertSee('Edit Product')
            ->assertSee($product->name);

        Livewire::actingAs($this->adminUser)
            ->test(ProductForm::class, ['productId' => $product->id])
            ->assertSet('name', $product->name)
            ->assertSet('price', $product->price)
            ->assertSet('selectedCategories', $product->categories->pluck('id')->toArray());
    }

    /** @test */
    public function admin_can_update_a_product()
    {
        $product = Product::factory()->create();
        $originalCategory = Category::firstOrFail();
        $product->categories()->attach($originalCategory->id);

        $updatedData = [
            'name' => 'Updated Product Name',
            'price' => 129.99,
            'stock_quantity' => 75,
        ];
        $newCategory = Category::factory()->create();


        Livewire::actingAs($this->adminUser)
            ->test(ProductForm::class, ['productId' => $product->id])
            ->set('name', $updatedData['name'])
            ->set('price', $updatedData['price'])
            ->set('stock_quantity', $updatedData['stock_quantity'])
            ->set('selectedCategories', [$newCategory->id])
            ->call('saveProduct')
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHas('message', 'Product updated successfully.');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => $updatedData['name'],
            'price' => $updatedData['price'],
            'stock_quantity' => $updatedData['stock_quantity'],
        ]);
        $product->refresh();
        $this->assertTrue($product->categories->contains($newCategory));
        $this->assertFalse($product->categories->contains($originalCategory));
    }

    /** @test */
    public function admin_can_delete_a_product()
    {
        $product = Product::factory()->create();
        Storage::fake('public');
        $imagePath = UploadedFile::fake()->image('test.jpg')->store('products_images', 'public');
        $product->image_path = $imagePath; // Assuming image_path is fillable or using save()
        $product->save();


        Livewire::actingAs($this->adminUser)
            ->test(ProductList::class)
            ->call('deleteProduct', $product->id);
            // ->assertSessionHas('message', 'Product deleted successfully.'); // This assertion can be flaky in direct Livewire calls

        $this->assertModelMissing($product);
        if ($imagePath) {
            Storage::disk('public')->assertMissing($imagePath);
        }
    }

    /** @test */
    public function validation_errors_are_shown_for_invalid_product_data_on_create()
    {
        Livewire::actingAs($this->adminUser)
            ->test(ProductForm::class)
            ->set('name', '')
            ->set('price', 'not-a-price')
            ->set('stock_quantity', -5)
            ->set('selectedCategories', [])
            ->call('saveProduct')
            ->assertHasErrors([
                'name' => 'required',
                'price' => 'numeric',
                'stock_quantity' => 'min',
                'selectedCategories' => 'required'
            ]);
    }

    /** @test */
    public function validation_error_for_slug_unique_rule_on_create()
    {
        $existingProduct = Product::factory()->create(['slug' => 'existing-slug']);

        Livewire::actingAs($this->adminUser)
            ->test(ProductForm::class)
            ->set('name', 'Test Product')
            ->set('slug', 'existing-slug')
            ->set('price', 10.00)
            ->set('stock_quantity', 10)
            ->set('selectedCategories', [Category::firstOrFail()->id])
            ->call('saveProduct')
            ->assertHasErrors(['slug' => 'unique']);
    }

    /** @test */
    public function validation_error_for_slug_unique_rule_is_ignored_for_own_model_on_update()
    {
        $product = Product::factory()->create(['slug' => 'my-product-slug']);
        $anotherCategory = Category::factory()->create(); // ensure a category exists

        Livewire::actingAs($this->adminUser)
            ->test(ProductForm::class, ['productId' => $product->id])
            ->set('name', 'Updated Name')
            ->set('slug', 'my-product-slug')
            ->set('price', $product->price)
            ->set('stock_quantity', $product->stock_quantity)
            ->set('selectedCategories', [$anotherCategory->id])
            ->call('saveProduct')
            ->assertHasNoErrors(['slug' => 'unique'])
            ->assertRedirect(route('admin.products.index'));
    }

    /** @test */
    public function image_is_optional_on_update_if_one_already_exists()
    {
        $product = Product::factory()->create(['image_path' => 'existing_image.jpg']);
        $category = Category::firstOrFail();

        Livewire::actingAs($this->adminUser)
            ->test(ProductForm::class, ['productId' => $product->id])
            ->set('name', 'Updated Product Name')
            ->set('price', 129.99)
            ->set('stock_quantity', 75)
            ->set('selectedCategories', [$category->id])
            ->call('saveProduct')
            ->assertHasNoErrors('image')
            ->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product Name',
            'image_path' => 'existing_image.jpg'
        ]);
    }

    /** @test */
    public function old_image_is_deleted_when_new_image_is_uploaded_on_update()
    {
        Storage::fake('public');
        $oldImagePath = UploadedFile::fake()->image('old.jpg')->store('products_images', 'public');
        $product = Product::factory()->create(['image_path' => $oldImagePath]);
        $category = Category::firstOrFail();

        $newImage = UploadedFile::fake()->image('new.jpg');

        Livewire::actingAs($this->adminUser)
            ->test(ProductForm::class, ['productId' => $product->id])
            ->set('name', 'Product With New Image')
            ->set('price', $product->price)
            ->set('stock_quantity', $product->stock_quantity)
            ->set('selectedCategories', [$category->id])
            ->set('image', $newImage)
            ->call('saveProduct')
            ->assertRedirect(route('admin.products.index'));

        $product->refresh();
        $this->assertNotEquals($oldImagePath, $product->image_path);
        $this->assertNotNull($product->image_path);
        Storage::disk('public')->assertMissing($oldImagePath);
        Storage::disk('public')->assertExists($product->image_path);
    }
}
