<?php

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    // Filtros
    public string $search = '';

    public string $categoryId = '';

    public string $brandId = '';

    // Carrito
    public array $cart = [];

    public string $cartMessage = '';

    // Límite máximo de unidades en el carrito
    private const MAX_CART_ITEMS = 10;

    public function mount(): void
    {
        $this->cart = session()->get('shopping_cart', []);
    }

    /**
     * Obtener productos aplicando los filtros.
     */
    #[Computed]
    public function products()
    {
        return Product::query()
            ->with(['category', 'brand'])
            ->where('is_active', true)

            ->when($this->search !== '', function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })

            ->when($this->categoryId !== '', function ($query) {
                $query->where('category_id', $this->categoryId);
            })

            ->when($this->brandId !== '', function ($query) {
                $query->where('brand_id', $this->brandId);
            })

            ->latest()
            ->paginate(12);
    }

    /**
     * Obtener categorías disponibles.
     */
    #[Computed]
    public function categories()
    {
        return Category::query()
            ->orderBy('name')
            ->get();
    }

    /**
     * Obtener marcas disponibles.
     */
    #[Computed]
    public function brands()
    {
        return Brand::query()
            ->orderBy('name')
            ->get();
    }

    /**
     * Cantidad total de unidades en el carrito.
     */
    #[Computed]
    public function cartCount(): int
    {
        return array_sum($this->cart);
    }

    /**
     * Agregar un producto al carrito.
     */

    public function addToCart(int $productId): void
    {
        $this->cartMessage = '';

        // Recuperar el carrito desde la sesión del servidor.
        $this->cart = session()->get('shopping_cart', []);

        // Verificar que el producto exista y esté activo.
        $product = Product::query()
            ->where('is_active', true)
            ->find($productId);

        if (!$product) {
            $this->cartMessage = 'El producto no está disponible.';
            return;
        }

        // Comprobar el límite máximo de 10 unidades.
        if (array_sum($this->cart) >= 10) {
            $this->cartMessage = 'Has alcanzado el límite de 10 productos.';
            return;
        }

        // Agregar una unidad.
        $this->cart[$productId] = ($this->cart[$productId] ?? 0) + 1;

        // Guardar en sesión.
        session()->put('shopping_cart', $this->cart);

        // Actualizar el indicador global del carrito.
        $this->dispatch('cart-updated');

        $this->cartMessage = 'Producto agregado correctamente.';
    }

    /**
     * Limpiar todos los filtros.
     */
    public function clearFilters(): void
    {
        $this->reset(['search', 'categoryId', 'brandId']);

        $this->resetPage();
    }

    /**
     * Reiniciar paginación al modificar los filtros.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryId(): void
    {
        $this->resetPage();
    }

    public function updatedBrandId(): void
    {
        $this->resetPage();
    }
};
?>

<div class="m-5">

    {{-- Header --}}
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">

            <div class="col">
                <h2 class="page-title">
                    Catálogo de productos
                </h2>

                <div class="text-secondary mt-1">
                    Explora nuestros productos y encuentra lo que necesitas.
                </div>
            </div>

        </div>
    </div>

    {{-- Mensajes --}}
    @if ($cartMessage !== '')
    <div
        class="alert {{ str_contains($cartMessage, 'correctamente') ? 'alert-success' : 'alert-warning' }} alert-dismissible mb-3"
        role="alert">
        <div class="d-flex">
            <div>
                <i class="ti ti-info-circle me-2"></i>
                {{ $cartMessage }}
            </div>
        </div>

        <button
            type="button"
            class="btn-close"
            wire:click="$set('cartMessage', '')"
            aria-label="Cerrar"></button>
    </div>
    @endif

    {{-- Filtros --}}
    <div class="card mb-4 bg-white">
        <div class="card-body">

            <div class="row g-3">

                {{-- Buscador --}}
                <div class="col-12 col-md-6 col-lg-4">
                    <label class="form-label">
                        Buscar productos
                    </label>

                    <div class="input-icon">
                        <span class="input-icon-addon">
                            <i class="ti ti-search"></i>
                        </span>

                        <input
                            type="search"
                            class="form-control"
                            placeholder="Nombre o descripción..."
                            wire:model.live.debounce.400ms="search">
                    </div>
                </div>

                {{-- Categoría --}}
                <div class="col-12 col-md-3 col-lg-3">
                    <label class="form-label">
                        Categoría
                    </label>

                    <select
                        class="form-select"
                        wire:model.live="categoryId">
                        <option value="">Todas las categorías</option>

                        @foreach ($this->categories as $category)
                        <option value="{{ $category->id }}">
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Marca --}}
                <div class="col-12 col-md-3 col-lg-3">
                    <label class="form-label">
                        Marca
                    </label>

                    <select
                        class="form-select"
                        wire:model.live="brandId">
                        <option value="">Todas las marcas</option>

                        @foreach ($this->brands as $brand)
                        <option value="{{ $brand->id }}">
                            {{ $brand->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Limpiar filtros --}}
                <div class="col-12 col-lg-2 d-flex align-items-end">
                    <button
                        type="button"
                        class="btn btn-outline-secondary w-100"
                        wire:click="clearFilters">
                        <i class="ti ti-refresh me-1"></i>
                        Limpiar filtros
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- Resultados --}}
    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>
            <h3 class="h3 mb-0">
                Nuestros productos
            </h3>

            <span class="text-secondary small">
                {{ $this->products->total() }} productos encontrados
            </span>
        </div>

        <div wire:loading class="text-secondary small">
            <i class="ti ti-loader-2 me-1"></i>
            Buscando...
        </div>

    </div>

    {{-- Cards --}}
    <div class="row row-cards">

        @forelse ($this->products as $product)

        <div
            class="col-12 col-sm-6 col-lg-4 col-xl-3"
            wire:key="product-{{ $product->id }}">
            <div class="card bg-white h-100 product-card">

                {{-- Imagen --}}
                <div class="product-image-container">

                    @if ($product->image)
                    <img
                        src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->image) }}"
                        alt="{{ $product->name }}"
                        class=""
                        loading="lazy">
                    @else
                    <div class="product-image-placeholder">
                        <i class="ti ti-photo"></i>
                        <span>Sin imagen</span>
                    </div>
                    @endif

                </div>

                {{-- Información --}}
                <div class="card-body d-flex flex-column">

                    {{-- Marca --}}
                    <div class="text-secondary small mb-1">
                        {{ $product->brand?->name ?? 'Sin marca' }}
                    </div>

                    {{-- Nombre --}}
                    <h3 class="h3 mb-2">
                        {{ $product->name }}
                    </h3>

                    {{-- Categoría --}}
                    @if ($product->category)
                    <div class="mb-2">
                        <span class="badge bg-azure-lt">
                            {{ $product->category->name }}
                        </span>
                    </div>
                    @endif

                    {{-- Descripción --}}
                    <p class="text-secondary small mb-3 product-description">
                        {{ $product->description ?: 'Sin descripción disponible.' }}
                    </p>

                    {{-- Precio --}}
                    <div class="mt-auto">

                        <div class="h2 text-primary mb-3">
                            S/ {{ number_format((float) $product->price, 2) }}
                        </div>

                        {{-- Agregar al carrito --}}
                        <button
                            type="button"
                            class="btn btn-primary w-100"
                            wire:click="addToCart({{ $product->id }})"
                            wire:loading.attr="disabled"
                            @disabled($this->cartCount >= 10)
                            >
                            <i class="ti ti-shopping-cart-plus me-2"></i>

                            @if ($this->cartCount >= 10)
                            Límite alcanzado
                            @else
                            Agregar al carrito
                            @endif
                        </button>

                    </div>

                </div>
            </div>
        </div>

        @empty

        {{-- Sin resultados --}}
        <div class="col-12">
            <div class="card bg-white">
                <div class="card-body text-center py-5">

                    <div class="empty-icon mb-3">
                        <i class="ti ti-package-off"></i>
                    </div>

                    <h3 class="h3">
                        No se encontraron productos
                    </h3>

                    <p class="text-secondary">
                        No hay productos que coincidan con los filtros seleccionados.
                    </p>

                    <button
                        type="button"
                        class="btn btn-outline-primary"
                        wire:click="clearFilters">
                        Limpiar filtros
                    </button>

                </div>
            </div>
        </div>

        @endforelse

    </div>

    {{-- Paginación --}}
    <div class="mt-4">
        {{ $this->products->links() }}
    </div>

    {{-- Indicador de carga --}}
    <div
        wire:loading.flex
        wire:target="search, categoryId, brandId, clearFilters"
        class="position-fixed bottom-0 end-0 p-3"
        style="z-index: 1050;">
        <div class="badge bg-primary text-white p-2">
            <i class="ti ti-loader-2 me-2"></i>
            Actualizando catálogo...
        </div>
    </div>

</div>

<style>
    .product-card {
        border: 1px solid var(--tblr-border-color);
        border-radius: 8px;
        overflow: hidden;
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .product-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--tblr-box-shadow);
    }

    .product-image-container {
        width: 100%;
        height: 220px;
        background-color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        padding: 12px;
    }

    .product-image {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: contain;
        object-position: center;
    }

    .product-image-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        color: var(--tblr-secondary);
    }

    .product-image-placeholder i {
        font-size: 3rem;
    }

    .product-description {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 36px;
    }
</style>