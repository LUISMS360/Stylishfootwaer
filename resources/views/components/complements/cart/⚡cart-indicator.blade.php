<?php

use Livewire\Component;
use Livewire\Attributes\On;


new class extends Component
{
   public int $count = 0;

    public function mount(): void
    {
        $this->refreshCount();
    }

    #[On('cart-updated')]
    public function refreshCount(): void
    {
        $cart = session()->get('shopping_cart', []);

        $this->count = is_array($cart)
            ? array_sum($cart)
            : 0;
    }

};
?>
<div>
    <a href="{{ route('cart.index') }}" wire:navigate
       class="nav-link d-flex align-items-center gap-2 btn btn-outline-danger">

        <span class="position-relative">
            <i class="ti ti-shopping-cart fs-2"></i>

            @if ($count > 0)
                <span class="badge bg-danger badge-pill position-absolute top-0 start-100 translate-middle text-white ">
                    {{ $count }}
                </span>
            @endif
        </span>

        <span class="d-none d-lg-inline">
            Mi carrito
        </span>
    </a>
</div>