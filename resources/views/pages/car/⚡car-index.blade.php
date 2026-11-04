<?php

use App\Actions\Checkout\PlaceOrder;
use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    public array $selectedVariants = [];

    public bool $showPaymentModal = false;

    public string $shippingAddress = '';

    public string $cardholder = '';

    public string $cardNumber = '';

    public string $cardExpiry = '';

    public string $cardCvv = '';

    public function mount(): void
    {
        $cart = session()->get('shopping_cart', []);

        if (! is_array($cart) || empty($cart)) {
            return;
        }

        $products = Product::query()
            ->where('is_active', true)
            ->whereIn('id', array_keys($cart))
            ->with('productVariant')
            ->get();

        foreach ($products as $product) {
            $quantity = max(
                0,
                (int) ($cart[$product->id] ?? 0)
            );

            $variant = $product->productVariant
                ->first(
                    fn(ProductVariant $variant) =>
                    $variant->stock >= $quantity
                        && $quantity > 0
                );

            if ($variant) {
                $this->selectedVariants[$product->id] =
                    (string) $variant->id;
            }
        }
    }

    #[Computed]
    public function items()
    {
        $cart = session()->get('shopping_cart', []);

        if (! is_array($cart) || empty($cart)) {
            return collect();
        }

        return Product::query()
            ->where('is_active', true)
            ->whereIn('id', array_keys($cart))
            ->with([
                'category',
                'brand',
                'productVariant',
            ])
            ->get()
            ->map(function (Product $product) use ($cart) {
                $quantity = max(
                    0,
                    (int) ($cart[$product->id] ?? 0)
                );

                $product->cart_quantity = $quantity;

                $product->subtotal =
                    (float) $product->price * $quantity;

                $product->availableVariants =
                    $product->productVariant
                    ->filter(
                        fn(ProductVariant $variant) =>
                        $variant->stock >= $quantity
                            && $quantity > 0
                    )
                    ->values();

                return $product;
            })
            ->filter(
                fn(Product $product) =>
                $product->cart_quantity > 0
            )
            ->values();
    }

    #[Computed]
    public function total(): float
    {
        return (float) $this->items->sum('subtotal');
    }

    #[Computed]
    public function itemCount(): int
    {
        return (int) $this->items->sum('cart_quantity');
    }

    public function abrirModalPago(): void
    {
        $this->resetErrorBag();

        if ($this->items->isEmpty()) {
            $this->addError('checkout', 'Tu carrito está vacío.');
            return;
        }

        foreach ($this->items as $item) {
            $variantId = $this->selectedVariants[$item->id] ?? null;

            $validVariant = $item->availableVariants
                ->contains(
                    fn(ProductVariant $variant) =>
                    (string) $variant->id === (string) $variantId
                );

            if (! $validVariant) {
                $this->addError(
                    "selectedVariants.{$item->id}",
                    "Selecciona una variante disponible para {$item->name}."
                );
            }
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        $this->showPaymentModal = true;
    }

    public function comprar(PlaceOrder $placeOrder): void
    {
        $this->validate([
            'shippingAddress' => [
                'required',
                'string',
                'max:1000',
            ],
            'cardholder' => [
                'required',
                'string',
                'max:255',
            ],
            'cardNumber' => [
                'required',
                'digits_between:13,19',
            ],
            'cardExpiry' => [
                'required',
                'regex:/^(0[1-9]|1[0-2])\/\d{2}$/',
            ],
            'cardCvv' => [
                'required',
                'digits_between:3,4',
            ],
        ], [
            'shippingAddress.required' =>
            'Ingresa la dirección de envío.',
            'cardholder.required' =>
            'Ingresa el nombre del titular.',
            'cardNumber.digits_between' =>
            'Ingresa un número de tarjeta de 13 a 19 dígitos, sin espacios.',
            'cardExpiry.regex' =>
            'La fecha debe tener el formato MM/AA.',
            'cardCvv.digits_between' =>
            'El código de seguridad debe tener 3 o 4 dígitos.',
        ]);

        $user = auth()->user();

        if (! $user) {
            $this->addError(
                'checkout',
                'Debes iniciar sesión para completar tu compra.'
            );

            return;
        }

        try {
            $order = $placeOrder->execute(
                $user,
                $this->shippingAddress,
                $this->selectedVariants,
            );
        } catch (InsufficientStockException $exception) {
            $this->addError(
                'checkout',
                'El stock de una de las variantes cambió. Revisa tu carrito e inténtalo nuevamente.'
            );

            unset($this->items);

            return;
        }

        // No conservar datos de la tarjeta en el estado del componente.
        $this->reset([
            'cardholder',
            'cardNumber',
            'cardExpiry',
            'cardCvv',
            'selectedVariants',
        ]);

        $this->showPaymentModal = false;

        unset($this->items);

        session()->flash(
            'success',
            "Pedido #{$order->id} registrado correctamente. Te enviaremos la confirmación por correo."
        );
    }
};
?>

<div>
    <div class="container-xl py-4">
        @if (session()->has('success'))
        <div
            class="alert alert-success alert-dismissible mb-4"
            role="alert">
            <div class="d-flex">
                <div>
                    <i class="ti ti-circle-check me-2"></i>
                </div>

                <div>
                    {{ session('success') }}
                </div>
            </div>
        </div>
        @endif
        {{-- Encabezado --}}
        <div class="page-header d-print-none mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <div class="page-pretitle">
                        Tienda
                    </div>

                    <h2 class="page-title">
                        <i class="ti ti-shopping-cart me-2"></i>
                        Mi carrito
                    </h2>

                    <div class="text-secondary mt-1">
                        Revisa tus productos antes de continuar con la compra.
                    </div>
                </div>

                <div class="col-auto ms-auto">
                    <span class="badge bg-primary-lt">
                        {{ $this->itemCount }} unidades
                    </span>
                </div>
            </div>
        </div>

        @if ($this->items->isEmpty())

        {{-- Carrito vacío --}}
        <div class="card">
            <div class="card-body py-5 text-center">
                <div class="empty">
                    <div class="empty-img">
                        <i class="ti ti-shopping-cart-off"
                            style="font-size: 5rem;"></i>
                    </div>

                    <p class="empty-title">
                        Tu carrito está vacío
                    </p>

                    <p class="empty-subtitle text-secondary">
                        Explora nuestros productos y agrega tus favoritos.
                    </p>

                    <div class="empty-action">
                        <a href="{{ url('/productos') }}"
                            wire:navigate
                            class="btn btn-primary">
                            <i class="ti ti-shopping-bag me-2"></i>
                            Explorar productos
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @else

        <div class="row row-cards">

            {{-- Listado de productos --}}
            <div class="col-12 col-lg-8">
                <div class="card">

                    <div class="card-header">
                        <h3 class="card-title">
                            Productos seleccionados
                        </h3>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-end">Precio</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($this->items as $item)
                                <tr wire:key="cart-item-{{ $item->id }}">

                                    {{-- Imagen y datos del producto --}}
                                    <td>
                                        <div class="d-flex align-items-center gap-3">

                                            @if ($item->image)
                                            <span
                                                class="avatar avatar-xl rounded bg-white border"
                                                style="background-image: url('{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->image) }}'); background-size: contain; background-repeat: no-repeat; background-position: center; flex-shrink: 0;"
                                                role="img"
                                                aria-label="{{ $item->name }}">
                                            </span>
                                            @else
                                            <span class="avatar avatar-xl rounded bg-light">
                                                <i class="ti ti-photo fs-2"></i>
                                            </span>
                                            @endif

                                            <div class="flex-fill">
                                                <div class="fw-bold">
                                                    {{ $item->name }}
                                                </div>

                                                <div class="text-secondary small">
                                                    {{ $item->brand?->name ?? 'Sin marca' }}
                                                </div>

                                                <div class="text-secondary small">
                                                    {{ $item->category?->name ?? 'Sin categoría' }}
                                                </div>

                                                {{-- Selector de variante --}}
                                                <div class="mt-3" style="max-width: 280px;">
                                                    <label
                                                        for="variant-{{ $item->id }}"
                                                        class="form-label small fw-semibold mb-1">
                                                        <i class="ti ti-ruler-measure me-1"></i>
                                                        Talla y color
                                                    </label>

                                                    <select
                                                        id="variant-{{ $item->id }}"
                                                        class="form-select form-select-sm"
                                                        wire:model="selectedVariants.{{ $item->id }}"
                                                        wire:key="variant-select-{{ $item->id }}">
                                                        <option value="">Selecciona una variante</option>

                                                        @foreach ($item->availableVariants as $variant)
                                                        <option
                                                            value="{{ $variant->id }}"
                                                            wire:key="variant-option-{{ $variant->id }}">
                                                            Talla {{ $variant->size }}
                                                            · {{ $variant->color }}
                                                            · Stock: {{ $variant->stock }}
                                                        </option>
                                                        @endforeach
                                                    </select>

                                                    @error("selectedVariants.{$item->id}")
                                                    <div class="text-danger small mt-1">
                                                        {{ $message }}
                                                    </div>
                                                    @enderror

                                                    @if ($item->availableVariants->isEmpty())
                                                    <div class="text-danger small mt-1">
                                                        <i class="ti ti-alert-circle me-1"></i>
                                                        No hay variantes con stock suficiente para esta cantidad.
                                                    </div>
                                                    @endif
                                                </div>

                                            </div>
                                        </div>
                                    </td>

                                    {{-- Cantidad --}}
                                    <td class="text-center">
                                        <span class="badge bg-secondary-lt">
                                            {{ $item->cart_quantity }}
                                        </span>
                                    </td>

                                    {{-- Precio unitario --}}
                                    <td class="text-end text-nowrap">
                                        S/ {{ number_format((float) $item->price, 2) }}
                                    </td>

                                    {{-- Subtotal --}}
                                    <td class="text-end fw-bold text-nowrap">
                                        S/ {{ number_format((float) $item->subtotal, 2) }}
                                    </td>

                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer">
                        <a href="{{ url('/productos') }}"
                            wire:navigate
                            class="btn btn-outline-secondary">
                            <i class="ti ti-arrow-left me-2"></i>
                            Seguir comprando
                        </a>
                    </div>

                </div>
            </div>

            {{-- Resumen de compra --}}
            <div class="col-12 col-lg-4">
                <div class="card">

                    <div class="card-header">
                        <h3 class="card-title">
                            Resumen de compra
                        </h3>
                    </div>

                    <div class="card-body">

                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-secondary">
                                Productos
                            </span>

                            <span class="fw-medium">
                                {{ $this->itemCount }}
                            </span>
                        </div>

                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-secondary">
                                Subtotal
                            </span>

                            <span>
                                S/ {{ number_format((float) $this->total, 2) }}
                            </span>
                        </div>

                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-secondary">
                                Envío
                            </span>

                            <span class="text-secondary">
                                Por confirmar
                            </span>
                        </div>

                        <div class="hr-text my-3"></div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <span class="h3 mb-0">
                                Total a pagar
                            </span>

                            <span class="h2 mb-0 text-primary">
                                S/ {{ number_format((float) $this->total, 2) }}
                            </span>
                        </div>

                        <button
                            type="button"
                            class="btn btn-primary w-100"
                            wire:click="abrirModalPago"
                            wire:loading.attr="disabled"
                            wire:target="abrirModalPago"
                            @disabled($this->items->isEmpty())
                            >
                            <i class="ti ti-lock me-2"></i>
                            Proceder a la compra
                        </button>

                        @error('checkout')
                        <div class="alert alert-danger mt-3 mb-0" role="alert">
                            {{ $message }}
                        </div>
                        @enderror

                        <div class="text-secondary text-center small mt-3">
                            <i class="ti ti-shield-check me-1"></i>
                            Pago de demostración; todavía no se realizará ningún cobro real.
                        </div>

                    </div>
                </div>
            </div>

        </div>

        @endif

    </div>
    @if ($showPaymentModal)
    <div
        class="modal modal-blur fade show d-block"
        tabindex="-1"
        role="dialog"
        aria-modal="true"
        wire:keydown.escape.window="$set('showPaymentModal', false)">
        <div
            class="modal-dialog modal-dialog-centered modal-lg"
            role="document">
            <div class="modal-content">

                {{-- Encabezado --}}
                <div class="modal-header">
                    <div>
                        <h3 class="modal-title">
                            <i class="ti ti-wallet me-2 text-primary"></i>
                            Finalizar compra
                        </h3>

                        <div class="text-secondary small mt-1">
                            Completa tus datos para simular el pago.
                        </div>
                    </div>

                    <button
                        type="button"
                        class="btn-close"
                        aria-label="Cerrar"
                        wire:click="$set('showPaymentModal', false)"
                        wire:loading.attr="disabled"></button>
                </div>

                <form wire:submit="comprar">
                    <div class="modal-body">

                        @error('checkout')
                        <div class="alert alert-danger" role="alert">
                            <i class="ti ti-alert-circle me-2"></i>
                            {{ $message }}
                        </div>
                        @enderror

                        <div class="alert alert-info" role="alert">
                            <div class="d-flex gap-2">
                                <i class="ti ti-info-circle fs-2"></i>

                                <div>
                                    <strong>Modo de demostración</strong>
                                    <div class="small">
                                        No introduzcas datos de una tarjeta real.
                                        Este formulario no procesa pagos ni
                                        almacena la información de la tarjeta.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">

                            {{-- Dirección de envío --}}
                            <div class="col-12">
                                <label
                                    for="shippingAddress"
                                    class="form-label required">
                                    Dirección de envío
                                </label>

                                <textarea
                                    id="shippingAddress"
                                    class="form-control @error('shippingAddress') is-invalid @enderror"
                                    wire:model="shippingAddress"
                                    rows="2"
                                    maxlength="1000"
                                    placeholder="Ingresa tu dirección completa"
                                    required></textarea>

                                @error('shippingAddress')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="hr-text">
                                    Datos de la tarjeta de demostración
                                </div>
                            </div>

                            {{-- Titular --}}
                            <div class="col-12">
                                <label
                                    for="cardholder"
                                    class="form-label required">
                                    Nombre del titular
                                </label>

                                <input
                                    id="cardholder"
                                    type="text"
                                    class="form-control @error('cardholder') is-invalid @enderror"
                                    wire:model="cardholder"
                                    maxlength="255"
                                    autocomplete="off"
                                    placeholder="NOMBRE DEL TITULAR"
                                    required>

                                @error('cardholder')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            {{-- Número de tarjeta --}}
                            <div class="col-12">
                                <label
                                    for="cardNumber"
                                    class="form-label required">
                                    Número de tarjeta
                                </label>

                                <div class="input-icon">
                                    <span class="input-icon-addon">
                                        <i class="ti ti-credit-card"></i>
                                    </span>

                                    <input
                                        id="cardNumber"
                                        type="text"
                                        inputmode="numeric"
                                        class="form-control @error('cardNumber') is-invalid @enderror"
                                        wire:model="cardNumber"
                                        maxlength="19"
                                        autocomplete="off"
                                        placeholder="1234567890123456"
                                        required>
                                </div>

                                @error('cardNumber')
                                <div class="text-danger small mt-1">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            {{-- Vencimiento --}}
                            <div class="col-6">
                                <label
                                    for="cardExpiry"
                                    class="form-label required">
                                    Vencimiento
                                </label>

                                <input
                                    id="cardExpiry"
                                    type="text"
                                    class="form-control @error('cardExpiry') is-invalid @enderror"
                                    wire:model="cardExpiry"
                                    maxlength="5"
                                    autocomplete="off"
                                    placeholder="MM/AA"
                                    required>

                                @error('cardExpiry')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            {{-- CVV --}}
                            <div class="col-6">
                                <label
                                    for="cardCvv"
                                    class="form-label required">
                                    Código de seguridad
                                </label>

                                <input
                                    id="cardCvv"
                                    type="password"
                                    inputmode="numeric"
                                    class="form-control @error('cardCvv') is-invalid @enderror"
                                    wire:model="cardCvv"
                                    maxlength="4"
                                    autocomplete="off"
                                    placeholder="123"
                                    required>

                                @error('cardCvv')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            {{-- Resumen --}}
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-secondary">
                                                Total de la compra
                                            </span>

                                            <span class="h2 text-primary mb-0">
                                                S/
                                                {{ number_format((float) $this->total, 2) }}
                                            </span>
                                        </div>

                                        <div class="text-secondary small mt-2">
                                            {{ $this->itemCount }}
                                            unidades seleccionadas
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- Acciones --}}
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            wire:click="$set('showPaymentModal', false)"
                            wire:loading.attr="disabled">
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            class="btn btn-primary"
                            wire:loading.attr="disabled"
                            wire:target="comprar">
                            <span
                                wire:loading.remove
                                wire:target="comprar">
                                <i class="ti ti-check me-2"></i>
                                Confirmar compra
                            </span>

                            <span
                                wire:loading
                                wire:target="comprar">
                                <span
                                    class="spinner-border spinner-border-sm me-2"
                                    role="status"></span>
                                Procesando...
                            </span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <div
        class="modal-backdrop fade show"
        wire:click="$set('showPaymentModal', false)"></div>
    @endif
</div>