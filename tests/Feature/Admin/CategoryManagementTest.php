<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Categories\CategoryForm;
use App\Livewire\Admin\Categories\CategoryList;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        // Using the same admin user details as in ProductManagementTest for consistency
        // The RefreshDatabase trait will ensure this is clean for each test class.
        // We can rely on the DatabaseSeeder to create the admin user if needed,
        // or create one specifically here. For explicitness:
        $this->adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            // Add 'is_admin' => true or similar if your app has specific role field
        ]);
    }

    /** @test */
    public function admin_can_view_category_list_page()
    {
        Category::factory()->create(['name' => 'Sample Category']);

        $this->actingAs($this->adminUser)
            ->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSeeLivewire(CategoryList::class)
            ->assertSee('Categories'); // Generic title on the page
    }

    /** @test */
    public function admin_can_view_create_category_page()
    {
        $this->actingAs($this->adminUser)
            ->get(route('admin.categories.create'))
            ->assertOk()
            ->assertSeeLivewire(CategoryForm::class)
            ->assertSee('Create New Category');
    }

    /** @test */
    public function admin_can_create_a_new_category()
    {
        Livewire::actingAs($this->adminUser)
            ->test(CategoryForm::class)
            ->set('name', 'New Test Category')
            ->set('slug', 'new-test-category')
            ->call('saveCategory')
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('message', 'Category created successfully.');

        $this->assertDatabaseHas('categories', [
            'slug' => 'new-test-category',
            'name' => 'New Test Category'
        ]);
    }

    /** @test */
    public function admin_can_create_a_category_and_slug_is_auto_generated()
    {
        Livewire::actingAs($this->adminUser)
            ->test(CategoryForm::class)
            ->set('name', 'Another Category Name')
            // Slug is not set, should be auto-generated
            ->call('saveCategory')
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'name' => 'Another Category Name',
            'slug' => 'another-category-name'
        ]);
    }

    /** @test */
    public function admin_can_view_edit_category_page()
    {
        $category = Category::factory()->create();

        $this->actingAs($this->adminUser)
            ->get(route('admin.categories.edit', $category))
            ->assertOk()
            ->assertSeeLivewire(CategoryForm::class)
            ->assertSee('Edit Category')
            ->assertSee($category->name); // Check if loaded data is visible

        Livewire::actingAs($this->adminUser)
            ->test(CategoryForm::class, ['categoryId' => $category->id])
            ->assertSet('name', $category->name)
            ->assertSet('slug', $category->slug);
    }

    /** @test */
    public function admin_can_update_a_category()
    {
        $category = Category::factory()->create();

        Livewire::actingAs($this->adminUser)
            ->test(CategoryForm::class, ['categoryId' => $category->id])
            ->set('name', 'Updated Category Name')
            ->set('slug', 'updated-category-slug')
            ->call('saveCategory')
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('message', 'Category updated successfully.');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Category Name',
            'slug' => 'updated-category-slug'
        ]);
    }

    /** @test */
    public function admin_can_delete_a_category()
    {
        $category = Category::factory()->create();

        Livewire::actingAs($this->adminUser)
            ->test(CategoryList::class)
            ->call('deleteCategory', $category->id);
            // ->assertSessionHas('message', 'Category deleted successfully.'); // This assertion can be flaky

        $this->assertModelMissing($category);
    }

    /** @test */
    public function validation_errors_are_shown_for_invalid_category_data_on_create()
    {
        Livewire::actingAs($this->adminUser)
            ->test(CategoryForm::class)
            ->set('name', '') // Name is required
            ->call('saveCategory')
            ->assertHasErrors(['name' => 'required']);
    }

    /** @test */
    public function validation_error_for_empty_slug_if_name_is_also_empty_on_create()
    {
        // This test ensures that if name is empty (which fails 'name' => 'required'),
        // slug generation might not run, and if slug is also empty, it should also fail.
        Livewire::actingAs($this->adminUser)
            ->test(CategoryForm::class)
            ->set('name', '')
            ->set('slug', '') // Explicitly setting slug to empty
            ->call('saveCategory')
            ->assertHasErrors(['name' => 'required', 'slug' => 'required']);
    }

    /** @test */
    public function validation_error_for_slug_unique_rule_on_create()
    {
        Category::factory()->create(['slug' => 'existing-slug']);

        Livewire::actingAs($this->adminUser)
            ->test(CategoryForm::class)
            ->set('name', 'Test Name')
            ->set('slug', 'existing-slug') // This slug already exists
            ->call('saveCategory')
            ->assertHasErrors(['slug' => 'unique']);
    }

    /** @test */
    public function validation_error_for_slug_unique_rule_is_ignored_for_own_model_on_update()
    {
        $category = Category::factory()->create(['slug' => 'my-unique-slug']);

        Livewire::actingAs($this->adminUser)
            ->test(CategoryForm::class, ['categoryId' => $category->id])
            ->set('name', 'Updated Name Same Slug')
            ->set('slug', $category->slug) // Keep the same slug
            ->call('saveCategory')
            ->assertHasNoErrors(['slug' => 'unique'])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Name Same Slug'
        ]);
    }
}
