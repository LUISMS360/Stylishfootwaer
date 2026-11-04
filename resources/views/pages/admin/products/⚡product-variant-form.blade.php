
<?php

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app-dashboard')] class extends Component
{
    public int $product_id = 0;

    public string $size = '';

    public string $color = '';

    public int $stock = 0;

    public string $sku = '';

    /**
     * Reglas de validación del formulario.
     */
    protected function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],
            'size' => [
                'required',
                'string',
                'max:50',
            ],
            'color' => [
                'required',
                'string',
                'max:50',
            ],
            'stock' => [
                'required',
                'integer',
                'min:0',
            ],
            'sku' => [
                'required',
                'string',
                'max:255',
                'unique:product_variants,sku',
            ],
        ];
    }

    /**
     * Etiquetas para los mensajes de validación.
     */
    protected function validationAttributes(): array
    {
        return [
            'product_id' => 'producto',
            'size' => 'talla',
            'color' => 'color',
            'stock' => 'stock',
            'sku' => 'SKU',
        ];
    }

    /**
     * Registra una nueva variante de producto.
     */
    public function save(): void
    {
        $validated = $this->validate();

        ProductVariant::create($validated);

        session()->flash(
            'success',
            'La variante del producto se registró correctamente.'
        );

        $this->reset([
            'product_id',
            'size',
            'color',
            'sku',
        ]);

        $this->stock = 0;

        $this->resetValidation();
    }

    /**
     * Productos disponibles para asociar una variante.
     */
    public function getProductsProperty()
    {
        return Product::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();
    }
};
?>

<div class="products-page">
    <div class="container-xl py-4">

        {{-- Encabezado --}}
        <div class="page-header d-print-none rounded-3 mb-4">
            <div class="container-fluid">
                <div class="row align-items-center g-3">

                    <div class="col">
                        <div class="page-pretitle">
                            ADMINISTRACIÓN / CATÁLOGO
                        </div>

                        <h2 class="page-title mb-1">
                            Crear variante de producto
                        </h2>

                        <div class="text-secondary mt-1">
                            Registra tallas, colores y existencias
                            para los productos de tu catálogo.
                        </div>
                    </div>

                    <div class="col-12 col-sm-auto">
                        <a
                            href="{{ url('/admin/productos') }}"
                            wire:navigate
                            class="btn btn-outline-secondary w-100"
                        >
                            <i class="ti ti-arrow-left me-1"></i>
                            Volver al catálogo
                        </a>
                    </div>

                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="container-fluid">

                {{-- Mensaje de éxito --}}
                @if (session()->has('success'))
                    <div
                        class="alert alert-success alert-dismissible"
                        role="alert"
                    >
                        <div class="d-flex">
                            <div>
                                <i class="ti ti-circle-check me-2"></i>
                                {{ session('success') }}
                            </div>
                        </div>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="alert"
                            aria-label="Cerrar"
                        ></button>
                    </div>
                @endif

                {{-- Resumen --}}
                <div class="row row-cards mb-4">

                    <div class="col-12 col-md-6">
                        <div class="card product-summary-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <span class="avatar rounded bg-blue-lt">
                                        <i class="ti ti-package fs-2"></i>
                                    </span>

                                    <div class="ms-3">
                                        <div class="text-secondary">
                                            Relación
                                        </div>

                                        <div class="fw-semibold mt-1">
                                            Producto existente
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="card product-summary-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <span class="avatar rounded bg-green-lt">
                                        <i class="ti ti-box fs-2"></i>
                                    </span>

                                    <div class="ms-3">
                                        <div class="text-secondary">
                                            Control de inventario
                                        </div>

                                        <div class="fw-semibold mt-1">
                                            Stock por variante
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Formulario --}}
                <form wire:submit="save">

                    <div class="card product-table-card">

                        <div class="card-header">
                            <div>
                                <h3 class="card-title">
                                    <i class="ti ti-forms me-2 text-blue"></i>
                                    Información de la variante
                                </h3>

                                <div class="text-secondary small mt-1">
                                    Completa los campos para registrar
                                    una nueva presentación del producto.
                                </div>
                            </div>
                        </div>

                        <div class="card-body">

                            <div class="row g-4">

                                {{-- Producto --}}
                                <div class="col-12">
                                    <label
                                        for="product_id"
                                        class="form-label required"
                                    >
                                        Producto
                                    </label>

                                    <div class="input-icon">
                                        <span class="input-icon-addon">
                                            <i class="ti ti-package"></i>
                                        </span>

                                        <select
                                            id="product_id"
                                            class="form-select ps-5
                                                @error('product_id') is-invalid @enderror"
                                            wire:model="product_id"
                                            required
                                        >
                                            <option value="0">
                                                Selecciona un producto
                                            </option>

                                            @foreach ($this->products as $product)
                                                <option
                                                    value="{{ $product->id }}"
                                                >
                                                    {{ $product->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    @error('product_id')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                    <div class="form-hint">
                                        Selecciona el producto al que
                                        pertenecerá esta variante.
                                    </div>
                                </div>

                                {{-- Talla --}}
                                <div class="col-12 col-md-6">
                                    <label
                                        for="size"
                                        class="form-label required"
                                    >
                                        Talla
                                    </label>

                                    <div class="input-icon">
                                        <span class="input-icon-addon">
                                            <i class="ti ti-ruler-measure"></i>
                                        </span>

                                        <input
                                            id="size"
                                            type="text"
                                            class="form-control ps-5
                                                @error('size') is-invalid @enderror"
                                            placeholder="Ej. 38, 40, M o XL"
                                            wire:model="size"
                                            maxlength="50"
                                            required
                                        >
                                    </div>

                                    @error('size')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                    <div class="form-hint">
                                        Introduce la talla o tamaño
                                        correspondiente al producto.
                                    </div>
                                </div>

                                {{-- Color --}}
                                <div class="col-12 col-md-6">
                                    <label
                                        for="color"
                                        class="form-label required"
                                    >
                                        Color
                                    </label>

                                    <div class="input-icon">
                                        <span class="input-icon-addon">
                                            <i class="ti ti-palette"></i>
                                        </span>

                                        <input
                                            id="color"
                                            type="text"
                                            class="form-control ps-5
                                                @error('color') is-invalid @enderror"
                                            placeholder="Ej. Negro, Blanco, Azul"
                                            wire:model="color"
                                            maxlength="50"
                                            required
                                        >
                                    </div>

                                    @error('color')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                    <div class="form-hint">
                                        Especifica el color de esta variante.
                                    </div>
                                </div>

                                {{-- SKU --}}
                                <div class="col-12 col-md-6">
                                    <label
                                        for="sku"
                                        class="form-label required"
                                    >
                                        Código SKU
                                    </label>

                                    <div class="input-icon">
                                        <span class="input-icon-addon">
                                            <i class="ti ti-barcode"></i>
                                        </span>

                                        <input
                                            id="sku"
                                            type="text"
                                            class="form-control ps-5
                                                @error('sku') is-invalid @enderror"
                                            placeholder="Ej. ZAP-NEG-40"
                                            wire:model="sku"
                                            maxlength="255"
                                            autocomplete="off"
                                            required
                                        >
                                    </div>

                                    @error('sku')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                    <div class="form-hint">
                                        El SKU debe ser único para
                                        identificar esta variante.
                                    </div>
                                </div>

                                {{-- Stock --}}
                                <div class="col-12 col-md-6">
                                    <label
                                        for="stock"
                                        class="form-label required"
                                    >
                                        Stock inicial
                                    </label>

                                    <div class="input-icon">
                                        <span class="input-icon-addon">
                                            <i class="ti ti-box-seam"></i>
                                        </span>

                                        <input
                                            id="stock"
                                            type="number"
                                            class="form-control ps-5
                                                @error('stock') is-invalid @enderror"
                                            wire:model="stock"
                                            min="0"
                                            step="1"
                                            required
                                        >
                                    </div>

                                    @error('stock')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                    <div class="form-hint">
                                        Cantidad inicial disponible
                                        para esta combinación de talla y color.
                                    </div>
                                </div>

                            </div>
                        </div>

                        {{-- Pie del formulario --}}
                        <div class="card-footer">
                            <div
                                class="d-flex flex-column flex-sm-row
                                    justify-content-between align-items-sm-center gap-3"
                            >
                                <div class="text-secondary small">
                                    <i class="ti ti-info-circle me-1"></i>
                                    Verifica los datos antes de guardar.
                                </div>

                                <div class="d-flex flex-column flex-sm-row gap-2">

                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary"
                                        wire:click="$refresh"
                                        wire:loading.attr="disabled"
                                    >
                                        <i class="ti ti-refresh me-1"></i>
                                        Actualizar formulario
                                    </button>

                                    <button
                                        type="submit"
                                        class="btn btn-primary"
                                        wire:loading.attr="disabled"
                                        wire:target="save"
                                    >
                                        <span
                                            wire:loading.remove
                                            wire:target="save"
                                        >
                                            <i class="ti ti-device-floppy me-1"></i>
                                            Guardar variante
                                        </span>

                                        <span
                                            wire:loading
                                            wire:target="save"
                                        >
                                            <span
                                                class="spinner-border spinner-border-sm me-2"
                                                role="status"
                                            ></span>
                                            Guardando...
                                        </span>
                                    </button>

                                </div>
                            </div>
                        </div>

                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .products-page {
        --products-blue-50: #f4f8fd;
        --products-blue-100: #e2effc;
        --products-blue-200: #c9e0f7;
        --products-blue-600: #2878bd;
        --products-text: #34465b;
        --products-border: #dce7f2;

        background: #f6f9fd;
        min-height: 100%;
    }

    .products-page .page-header {
        padding-top: 1.5rem;
        padding-bottom: 1.5rem;
        background: linear-gradient(110deg, #fff, #f0f7ff);
        border-bottom: 1px solid var(--products-border);
    }

    .products-page .page-pretitle {
        color: #71849a;
        font-weight: 700;
        letter-spacing: .08em;
        font-size: .75rem;
    }

    .products-page .page-title {
        color: #293d52;
        font-weight: 700;
        letter-spacing: -.025em;
    }

    .products-page .card {
        height: 100%;
        border: 1px solid var(--products-border);
        border-radius: 12px;
        box-shadow: 0 2px 8px rgb(37 55 77 / 4%);
    }

    .products-page .card-header {
        padding: 1.15rem 1.25rem;
        border-bottom-color: #edf2f8;
    }

    .products-page .card-title {
        color: var(--products-text);
        font-weight: 650;
        margin-bottom: 0;
    }

    .products-page .card-body {
        padding: 1.5rem;
    }

    .products-page .card-footer {
        padding: 1rem 1.5rem;
        background: #fff;
        border-top: 1px solid #edf2f8;
        border-radius: 0 0 12px 12px;
    }

    .products-page .form-label {
        color: var(--products-text);
        font-weight: 600;
        margin-bottom: .5rem;
    }

    .products-page .form-control,
    .products-page .form-select {
        min-height: 42px;
        border-color: #d6e1ed;
        border-radius: 8px;
    }

    .products-page .form-control:focus,
    .products-page .form-select:focus {
        border-color: #8ebbe7;
        box-shadow: 0 0 0 .2rem rgb(71 143 208 / 12%);
    }

    .products-page .form-hint {
        margin-top: .4rem;
        color: #8493a5;
        font-size: .8rem;
        line-height: 1.5;
    }

    .products-page .input-icon-addon {
        color: #8296ac;
    }

    .products-page .btn {
        border-radius: 8px;
    }

    .products-page .btn-primary {
        background-color: var(--products-blue-600);
        border-color: var(--products-blue-600);
    }

    .products-page .btn-primary:hover {
        background-color: #2168a5;
        border-color: #2168a5;
    }

    .products-page .invalid-feedback {
        font-size: .8rem;
    }

    @media (max-width: 767.98px) {
        .products-page .page-header {
            padding-top: 1rem;
            padding-bottom: 1rem;
        }

        .products-page .card-header,
        .products-page .card-body,
        .products-page .card-footer {
            padding: 1rem;
        }
    }
</style>
@endpush
