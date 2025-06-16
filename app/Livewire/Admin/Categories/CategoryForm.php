<?php

namespace App\Livewire\Admin\Categories;

use App\Models\Category;
use Illuminate\Support\Str;
use Livewire\Component;

class CategoryForm extends Component
{
    public $categoryId;
    public $name = '';
    public $slug = '';

    public function mount($categoryId = null)
    {
        $this->categoryId = $categoryId;
        if ($this->categoryId) {
            $category = Category::findOrFail($this->categoryId);
            $this->name = $category->name;
            $this->slug = $category->slug;
        }
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug' . ($this->categoryId ? ',' . $this->categoryId : ''),
        ];
    }

    public function updatedName($value)
    {
        if (!$this->categoryId || empty($this->slug)) { // Only auto-generate slug for new categories or if slug is empty during edit
            $this->generateSlug();
        }
    }

    public function generateSlug()
    {
        $this->slug = Str::slug($this->name);
    }

    public function saveCategory()
    {
        $this->validate();

        if (empty($this->slug)) {
            $this->generateSlug();
             // Re-validate slug after auto-generation
            $this->validateOnly('slug');
        }

        $categoryData = [
            'name' => $this->name,
            'slug' => $this->slug,
        ];

        if ($this->categoryId) {
            $category = Category::findOrFail($this->categoryId);
            $category->update($categoryData);
        } else {
            Category::create($categoryData);
        }

        session()->flash('message', 'Category ' . ($this->categoryId ? 'updated' : 'created') . ' successfully.');
        return redirect()->route('admin.categories.index');
    }

    public function render()
    {
        return view('livewire.admin.categories.category-form')
            ->layout('layouts.admin');
    }
}
