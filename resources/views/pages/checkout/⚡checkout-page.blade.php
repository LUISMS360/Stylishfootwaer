<?php

use App\Actions\Checkout\PlaceOrder;
use App\Exceptions\InsufficientStockException;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    #[Validate('required|string|min:10|max:255')]
    public string $shippingAddress = '';

    public function placeOrder(PlaceOrder $action)
    {
        $this->validate();

        try {
            $order = $action->execute(auth()->user(), $this->shippingAddress);
        } catch (InsufficientStockException $e) {
            $this->addError('stock', $e->getMessage());
            return;
        }

        return $this->redirectRoute('orders.show', $order, navigate: true);
    }
};
?>

<div>
    {{-- You must be the change you wish to see in the world. - Mahatma Gandhi --}}
</div>