<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-semibold mb-4">{{ $productId ? 'Edit Product' : 'Create New Product' }}</h1>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <form wire:submit.prevent="saveProduct" class="bg-white shadow-md rounded-lg px-8 pt-6 pb-8 mb-4">
        @csrf
        <!-- Name -->
        <div class="mb-4">
            <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Name:</label>
            <input type="text" id="name" wire:model.lazy="name" wire:keyup="generateSlug"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('name') border-red-500 @enderror">
            @error('name') <p class="text-red-500 text-xs italic">{{ $message }}</p> @enderror
        </div>

        <!-- Slug -->
        <div class="mb-4">
            <label for="slug" class="block text-gray-700 text-sm font-bold mb-2">Slug:</label>
            <input type="text" id="slug" wire:model="slug"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('slug') border-red-500 @enderror">
            @error('slug') <p class="text-red-500 text-xs italic">{{ $message }}</p> @enderror
        </div>

        <!-- Description -->
        <div class="mb-4">
            <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Description:</label>
            <textarea id="description" wire:model="description" rows="4"
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('description') border-red-500 @enderror"></textarea>
            @error('description') <p class="text-red-500 text-xs italic">{{ $message }}</p> @enderror
        </div>

        <!-- Price -->
        <div class="mb-4">
            <label for="price" class="block text-gray-700 text-sm font-bold mb-2">Price:</label>
            <input type="number" id="price" wire:model="price" step="0.01"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('price') border-red-500 @enderror">
            @error('price') <p class="text-red-500 text-xs italic">{{ $message }}</p> @enderror
        </div>

        <!-- Stock Quantity -->
        <div class="mb-6">
            <label for="stock_quantity" class="block text-gray-700 text-sm font-bold mb-2">Stock Quantity:</label>
            <input type="number" id="stock_quantity" wire:model="stock_quantity"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('stock_quantity') border-red-500 @enderror">
            @error('stock_quantity') <p class="text-red-500 text-xs italic">{{ $message }}</p> @enderror
        </div>

        <!-- Categories -->
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2">Categories:</label>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($allCategories as $category)
                    <label class="inline-flex items-center">
                        <input type="checkbox" wire:model="selectedCategories" value="{{ $category->id }}"
                               class="form-checkbox h-5 w-5 text-blue-600">
                        <span class="ml-2 text-gray-700">{{ $category->name }}</span>
                    </label>
                @endforeach
            </div>
            @error('selectedCategories') <p class="text-red-500 text-xs italic">{{ $message }}</p> @enderror
        </div>


        <!-- Image Upload -->
        <div class="mb-6">
            <label for="image" class="block text-gray-700 text-sm font-bold mb-2">Product Image:</label>
            <input type="file" id="image" wire:model="image"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('image') border-red-500 @enderror">

            @if ($image)
                <div class="mt-2">
                    <p class="text-sm font-semibold">New image preview:</p>
                    <img src="{{ $image->temporaryUrl() }}" alt="Image Preview" class="w-32 h-32 object-cover mt-1 rounded">
                </div>
            @elseif ($existingImageUrl)
                <div class="mt-2">
                    <p class="text-sm font-semibold">Current image:</p>
                    <img src="{{ $existingImageUrl }}" alt="Current Product Image" class="w-32 h-32 object-cover mt-1 rounded">
                </div>
            @endif
            @error('image') <p class="text-red-500 text-xs italic">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center justify-between">
            <button type="submit"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Save Product
            </button>
            <a href="{{ route('admin.products.index') }}" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                Cancel
            </a>
        </div>
    </form>
</div>
