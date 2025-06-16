<?php

namespace App\Livewire\Admin\Categories;

use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;

class CategoryList extends Component
{
    use WithPagination;

    public function deleteCategory($categoryId)
    {
        $category = Category::findOrFail($categoryId);
        // TODO: Add authorization check
        // For now, we'll just delete the category.
        // Products associated via pivot table will have their category_id foreign keys
        // handled based on DB schema (cascade, set null, or restrict).
        // If restrict, deletion will fail if products are associated.
        $category->delete();

        session()->flash('message', 'Category deleted successfully.');
    }

    public function render()
    {
        return view('livewire.admin.categories.category-list', [
            'categories' => Category::withCount('products')->latest()->paginate(10),
        ])->layout('layouts.admin');
    }
}
