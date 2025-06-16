<?php

namespace App\Livewire\Admin\Products;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProductForm extends Component
{
    use WithFileUploads;

    public $productId;
    public $name = '';
    public $slug = '';
    public $description = '';
    public $price;
    public $stock_quantity;
    public $image; // Instance of UploadedFile
    public $existingImageUrl;
    public $selectedCategories = [];

    public function mount($productId = null)
    {
        $this->productId = $productId;
        if ($this->productId) {
            $product = Product::with('categories')->findOrFail($this->productId);
            $this->name = $product->name;
            $this->slug = $product->slug;
            $this->description = $product->description;
            $this->price = $product->price;
            $this->stock_quantity = $product->stock_quantity;
            $this->existingImageUrl = $product->image_path ? Storage::url($product->image_path) : null;
            $this->selectedCategories = $product->categories->pluck('id')->toArray();
        }
    }

    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products,slug' . ($this->productId ? ',' . $this->productId : ''),
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'selectedCategories' => 'required|array|min:1',
            'selectedCategories.*' => 'exists:categories,id',
        ];

        if ($this->image) { // If a new image is being uploaded
            $rules['image'] = 'image|mimes:jpeg,png,jpg,gif|max:2048'; // 2MB Max
        } elseif (!$this->productId && !$this->existingImageUrl) { // If creating a new product and no image is uploaded
            // $rules['image'] = 'required|image|mimes:jpeg,png,jpg,gif|max:2048'; // Optional: make image required for new products
        }

        return $rules;
    }

    public function updatedName($value)
    {
        if (!$this->productId) { // Only auto-generate slug for new products or if slug is empty
            $this->generateSlug();
        }
    }

    public function generateSlug()
    {
        $this->slug = Str::slug($this->name);
    }

    public function saveProduct()
    {
        $this->validate();

        if (empty($this->slug)) {
            $this->generateSlug();
            // Re-validate slug after auto-generation
            $this->validateOnly('slug');
        }

        $imagePath = $this->productId ? Product::findOrFail($this->productId)->image_path : null;

        if ($this->image) {
            // Delete old image if it exists and a new one is uploaded
            if ($this->productId && $imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            // Store new image
            $imagePath = $this->image->store('products_images', 'public');
        }

        $productData = [
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'stock_quantity' => $this->stock_quantity,
            'image_path' => $imagePath,
        ];

        if ($this->productId) {
            $product = Product::findOrFail($this->productId);
            $product->update($productData);
        } else {
            $product = Product::create($productData);
        }

        $product->categories()->sync($this->selectedCategories);

        session()->flash('message', 'Product ' . ($this->productId ? 'updated' : 'created') . ' successfully.');
        return redirect()->route('admin.products.index');
    }

    public function render()
    {
        return view('livewire.admin.products.product-form', [
            'allCategories' => Category::all(),
        ])->layout('layouts.admin'); // Assuming an admin layout exists
    }
}
