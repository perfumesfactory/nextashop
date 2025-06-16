<div>
    @if($product->stock_quantity > 0)
        <button wire:click="addToCart"
                class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition-colors duration-300 w-full text-sm">
            Add to Cart
        </button>
    @else
        <button class="bg-gray-300 text-gray-500 font-semibold py-2 px-4 rounded-lg shadow-md w-full text-sm" disabled>
            Out of Stock
        </button>
    @endif
</div>
