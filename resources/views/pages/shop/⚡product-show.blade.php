<?php

use App\Actions\Cart\AddToCart;
use App\Actions\Cart\ResolveCart;
use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    public Product $product;

    #[Validate('required|exists:product_variants,id', message: 'Elige una talla')]
    public ?int $variantId = null;

    public function mount(Product $product): void
    {
        $this->product = $product->load('brand', 'category');
    }

    #[Computed()]
    public function variants()
    {
        return $this->product->variants()->orderBy('size')->get();
    }

    public function addToCart(AddToCart $addToCart, ResolveCart $resolveCart): void
    {
        $this->validate();

        try {
            $addToCart->execute($resolveCart->execute(), $this->variantId);
        } catch (InsufficientStockException $e) {
            $this->addError('variantId', $e->getMessage());
            return;
        }

        $this->dispatch('cart-updated');
    }
};
?>

<div>
    <h1>Productos</h1>
</div>