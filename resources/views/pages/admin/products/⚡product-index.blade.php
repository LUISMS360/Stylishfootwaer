<?php

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;

new #[Layout('layouts.app-dashboard')] class extends Component
{
    use WithPagination, WithoutUrlPagination;

    public string $search = '';

    public string $status = '';

    public int $perPage = 10;

    /**
     * Reinicia la paginación cuando cambia la búsqueda.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Reinicia la paginación cuando cambia el estado.
     */
    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    /**
     * Reinicia la paginación cuando cambia el número de registros.
     */
    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    /**
     * Listado reactivo y paginado de productos.
     */
    #[Computed]
    public function products()
    {
        return Product::query()
            ->with([
                'category:id,name',
                'brand:id,name',
            ])
            ->when(trim($this->search) !== '', function (Builder $query) {
                $term = '%' . trim($this->search) . '%';

                $query->where(function (Builder $query) use ($term) {
                    $query->where('name', 'like', $term)
                        ->orWhere('slug', 'like', $term)
                        ->orWhereHas('category', function (Builder $category) use ($term) {
                            $category->where('name', 'like', $term);
                        })
                        ->orWhereHas('brand', function (Builder $brand) use ($term) {
                            $brand->where('name', 'like', $term);
                        });
                });
            })
            ->when($this->status !== '', function (Builder $query) {
                $query->where('is_active', $this->status === 'active');
            })
            ->select([
                'id',
                'name',
                'slug',
                'category_id',
                'brand_id',
                'description',
                'price',
                'image',
                'is_active',
                'created_at',
                'updated_at',
            ])
            ->latest('created_at')
            ->paginate($this->perPage);
    }

    /**
     * Elimina un producto.
     */
    public function deleteProduct(int $id): void
    {
        $product = Product::findOrFail($id);

        // Requiere una ProductPolicy con el método delete.
        $this->authorize('delete', $product);

        $image = $product->image;

        $product->delete();

        // Elimina la imagen si pertenece al disco público.
        if ($image && Storage::disk('public')->exists($image)) {
            Storage::disk('public')->delete($image);
        }

        unset($this->products);

        $this->resetPage();

        session()->flash('success', 'El producto se eliminó correctamente.');
    }
};
?>

<div class="container-xl py-4">

    {{-- Encabezado --}}
    <div class="page-header d-print-none">
        <div class="container-fluid">
            <div class="row align-items-center g-3">
                <div class="col">
                    <div class="page-pretitle">
                        ADMINISTRACIÓN / CATÁLOGO
                    </div>

                    <h2 class="page-title">
                        Gestión de productos
                    </h2>

                    <div class="text-secondary mt-1">
                        Administra tu catálogo de calzado, consulta productos
                        y controla su disponibilidad.
                    </div>
                </div>

                <div class="col-12 col-sm-auto d-flex gap-2">
                    <a href="{{ url('/admin/productos/crear') }}" wire:navigate
                        class="btn btn-primary w-100">
                        <i class="ti ti-plus me-1"></i>
                        Nuevo producto
                    </a>                    
                    <a href="{{ route('products.create-variant') }}" wire:navigate
                        class="btn btn-green w-100">
                        <i class="ti ti-plus me-1"></i>
                        Nueva Variante
                    </a>                    
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-fluid">

            {{-- Mensajes de respuesta --}}
            @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                <div class="d-flex">
                    <div>
                        <i class="ti ti-circle-check me-2"></i>
                        {{ session('success') }}
                    </div>
                </div>

                <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Cerrar"></button>
            </div>
            @endif

            {{-- Resumen del catálogo --}}
            <div class="row row-cards mb-4">

                <div class="col-12 col-sm-6 col-xl-4">
                    <div class="card product-summary-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar rounded bg-blue-lt">
                                    <i class="ti ti-package fs-2"></i>
                                </span>

                                <div class="ms-3">
                                    <div class="text-secondary">
                                        Productos encontrados
                                    </div>

                                    <div class="h2 mb-0">
                                        {{ $this->products->total() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-4">
                    <div class="card product-summary-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar rounded bg-green-lt">
                                    <i class="ti ti-circle-check fs-2"></i>
                                </span>

                                <div class="ms-3">
                                    <div class="text-secondary">
                                        Estado del filtro
                                    </div>

                                    <div class="fw-semibold mt-1">
                                        @if ($status === 'active')
                                        Solo activos
                                        @elseif ($status === 'inactive')
                                        Solo inactivos
                                        @else
                                        Todos los estados
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-4">
                    <div class="card product-summary-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar rounded bg-azure-lt">
                                    <i class="ti ti-search fs-2"></i>
                                </span>

                                <div class="ms-3">
                                    <div class="text-secondary">
                                        Búsqueda
                                    </div>

                                    <div class="fw-semibold mt-1">
                                        {{ trim($search) !== '' ? 'Filtro aplicado' : 'Sin búsqueda' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Tabla y filtros --}}
            <div class="card product-table-card">

                <div class="card-header">
                    <div>
                        <h3 class="card-title">
                            <i class="ti ti-list-details me-2 text-blue"></i>
                            Catálogo de productos
                        </h3>

                        <div class="text-secondary small mt-1">
                            Consulta, busca y administra los productos registrados.
                        </div>
                    </div>
                </div>

                {{-- Barra de búsqueda --}}
                <div class="card-body border-bottom">
                    <div class="row g-3 align-items-end">

                        <div class="col-12 col-lg-5">
                            <label for="product-search" class="form-label">
                                Buscar productos
                            </label>

                            <div class="input-icon">
                                <span class="input-icon-addon">
                                    <i class="ti ti-search"></i>
                                </span>

                                <input
                                    id="product-search"
                                    type="search"
                                    class="form-control"
                                    placeholder="Nombre, slug, marca o categoría..."
                                    wire:model.live.debounce.350ms="search"
                                    autocomplete="off">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <label for="product-status" class="form-label">
                                Estado
                            </label>

                            <select
                                id="product-status"
                                class="form-select"
                                wire:model.live="status">
                                <option value="">Todos los estados</option>
                                <option value="active">Activos</option>
                                <option value="inactive">Inactivos</option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-2">
                            <label for="product-per-page" class="form-label">
                                Registros
                            </label>

                            <select
                                id="product-per-page"
                                class="form-select"
                                wire:model.live="perPage">
                                <option value="10">10 por página</option>
                                <option value="25">25 por página</option>
                                <option value="50">50 por página</option>
                            </select>
                        </div>

                        <div class="col-12 col-lg-2">
                            <button
                                type="button"
                                class="btn btn-outline-secondary w-100"
                                wire:click="$set('search', '')"
                                wire:loading.attr="disabled">
                                <i class="ti ti-restore me-1"></i>
                                Limpiar búsqueda
                            </button>
                        </div>

                    </div>

                    {{-- Indicador de carga --}}
                    <div wire:loading.delay
                        wire:target="search,status,perPage,gotoPage,nextPage,previousPage"
                        class="text-secondary small mt-3">
                        <span class="spinner-border spinner-border-sm me-2"
                            role="status"></span>
                        Actualizando resultados...
                    </div>
                </div>

                {{-- Tabla responsive --}}
                <div class="table-responsive">
                    <table class="table table-vcenter card-table product-table">

                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Marca</th>
                                <th>Categoría</th>
                                <th>Precio</th>
                                <th>Estado</th>
                                <th>Registro</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>

                        <tbody wire:loading.class="opacity-50"
                            wire:target="search,status,perPage,gotoPage,nextPage,previousPage">

                            @forelse ($this->products as $product)
                            <tr wire:key="product-{{ $product->id }}">

                                {{-- Imagen y nombre --}}
                                <td class="product-cell">
                                    <div class="d-flex align-items-center gap-3 product-identity">

                                        @if ($product->image)
                                        <span class="product-image flex-shrink-0">
                                            <img
                                                src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->image) }}"
                                                alt="{{ $product->name }}"
                                                loading="lazy">
                                        </span>
                                        @else
                                        <span class="product-image product-image-empty flex-shrink-0">
                                            <i class="ti ti-shoe"></i>
                                        </span>
                                        @endif

                                        <div class="product-info">
                                            <div class="fw-semibold text-dark product-name">
                                                {{ $product->name }}
                                            </div>

                                            <div class="text-secondary small product-slug">
                                                {{ $product->slug }}
                                            </div>
                                        </div>

                                    </div>
                                </td>

                                {{-- Marca --}}
                                <td>
                                    <span class="text-secondary">
                                        {{ $product->brand?->name ?? 'Sin marca' }}
                                    </span>
                                </td>

                                {{-- Categoría --}}
                                <td>
                                    <span class="badge bg-azure-lt">
                                        {{ $product->category?->name ?? 'Sin categoría' }}
                                    </span>
                                </td>

                                {{-- Precio --}}
                                <td class="text-nowrap">
                                    <span class="fw-semibold text-dark">
                                        S/ {{ number_format((float) $product->price, 2) }}
                                    </span>
                                </td>

                                {{-- Estado --}}
                                <td>
                                    @if ($product->is_active)
                                    <span class="badge bg-green-lt">
                                        <span class="status-dot status-dot-animated bg-green me-1"></span>
                                        Activo
                                    </span>
                                    @else
                                    <span class="badge bg-secondary-lt">
                                        <span class="status-dot bg-secondary me-1"></span>
                                        Inactivo
                                    </span>
                                    @endif
                                </td>

                                {{-- Fecha --}}
                                <td class="text-nowrap">
                                    <div>
                                        {{ $product->created_at?->format('d/m/Y') ?? '—' }}
                                    </div>

                                    <div class="text-secondary small">
                                        {{ $product->created_at?->format('H:i') }}
                                    </div>
                                </td>

                                {{-- Acciones --}}
                                <td>
                                    <div class="d-flex justify-content-end gap-1">

                                        {{-- Actualizar --}}
                                        <a
                                            href="{{ url('/admin/productos/' . $product->id . '/editar') }}"
                                            class="btn btn-icon btn-azure"
                                            title="Actualizar producto"
                                            aria-label="Actualizar {{ $product->name }}">
                                            <i class="ti ti-edit"></i>
                                        </a>

                                        {{-- Eliminar --}}
                                        <button
                                            type="button"
                                            class="btn btn-icon btn-red"
                                            title="Eliminar producto"
                                            aria-label="Eliminar {{ $product->name }}"
                                            wire:click="deleteProduct({{ $product->id }})"
                                            wire:confirm="¿Estás seguro de eliminar el producto {{ $product->name }}? Esta acción no se puede deshacer."
                                            wire:loading.attr="disabled"
                                            wire:target="deleteProduct({{ $product->id }})">
                                            <i class="ti ti-trash"
                                                wire:loading.remove
                                                wire:target="deleteProduct({{ $product->id }})"></i>

                                            <span
                                                class="spinner-border spinner-border-sm"
                                                wire:loading
                                                wire:target="deleteProduct({{ $product->id }})"
                                                role="status"
                                                aria-label="Eliminando"></span>
                                        </button>

                                    </div>
                                </td>

                            </tr>
                            @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty py-5">
                                        <div class="empty-img">
                                            <i class="ti ti-package-off product-empty-icon"></i>
                                        </div>

                                        <p class="empty-title">
                                            No se encontraron productos
                                        </p>

                                        <p class="empty-subtitle text-secondary">
                                            @if (trim($search) !== '')
                                            Prueba con otro nombre, marca o categoría.
                                            @else
                                            Aún no hay productos que coincidan con los filtros seleccionados.
                                            @endif
                                        </p>

                                        @if (trim($search) !== '' || $status !== '')
                                        <button
                                            type="button"
                                            class="btn btn-outline-primary mt-2"
                                            wire:click="$set('search', '')">
                                            Limpiar búsqueda
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforelse

                        </tbody>
                    </table>
                </div>

                {{-- Pie y paginación --}}
                <div class="card-footer">
                    <div class="row align-items-center g-3">

                        <div class="col-12 col-md">
                            <div class="text-secondary small">
                                Mostrando
                                <strong>{{ $this->products->firstItem() ?? 0 }}</strong>
                                a
                                <strong>{{ $this->products->lastItem() ?? 0 }}</strong>
                                de
                                <strong>{{ $this->products->total() }}</strong>
                                productos
                            </div>
                        </div>

                        <div class="col-12 col-md-auto">
                            {{ $this->products->links() }}
                        </div>

                    </div>
                </div>

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
    }

    .products-page .page-title {
        color: #293d52;
        font-weight: 700;
        letter-spacing: -.025em;
    }

    .products-page .card {
        border: 1px solid var(--products-border);
        border-radius: 12px;
        box-shadow: 0 2px 8px rgb(37 55 77 / 4%);
    }

    .products-page .product-summary-card {
        height: 100%;
    }

    .products-page .product-table-card {
        overflow: hidden;
    }

    .products-page .card-header {
        padding: 1.15rem 1.25rem;
        border-bottom-color: #edf2f8;
    }

    .products-page .card-title {
        color: var(--products-text);
        font-weight: 650;
    }

    .products-page .form-control,
    .products-page .form-select {
        border-color: #d6e1ed;
        border-radius: 8px;
    }

    .products-page .form-control:focus,
    .products-page .form-select:focus {
        border-color: #8ebbe7;
        box-shadow: 0 0 0 .2rem rgb(71 143 208 / 12%);
    }

    .products-page .product-table {
        margin-bottom: 0;
    }

    .products-page .product-table thead th {
        padding: .9rem 1rem;
        background: #f7f9fc;
        color: #708196;
        font-size: .72rem;
        font-weight: 750;
        letter-spacing: .045em;
        text-transform: uppercase;
        white-space: nowrap;
        border-bottom: 1px solid var(--products-border);
    }

    .products-page .product-table tbody td {
        padding: .9rem 1rem;
        vertical-align: middle;
        border-bottom-color: #edf2f8;
    }

    .products-page .product-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .products-page .product-table tbody tr:hover {
        background: #f8fbff;
    }

    .products-page .product-image {
        display: inline-flex;
        flex: 0 0 54px;
        align-items: center;
        justify-content: center;
        width: 54px;
        height: 54px;
        overflow: hidden;
        border: 1px solid #e3ebf4;
        border-radius: 9px;
        background: #fff;
    }

    .products-page .product-image img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .products-page .product-image-empty {
        background: #edf5ff;
        color: #6d9bc8;
        font-size: 1.65rem;
    }

    .products-page .product-info {
        min-width: 120px;
        overflow-wrap: anywhere;
    }

    .products-page .product-info .fw-semibold {
        line-height: 1.4;
    }

    .products-page .btn-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 34px;
        height: 34px;
        padding: 0;
        border-radius: 8px;
    }

    .products-page .card-footer {
        background: #fff;
        border-top: 1px solid #edf2f8;
    }

    .products-page .pagination {
        flex-wrap: wrap;
        gap: 3px;
        margin-bottom: 0;
    }

    .products-page .pagination .page-link {
        border-radius: 6px;
    }

    .products-page .empty {
        text-align: center;
    }

    .products-page .product-empty-icon {
        color: #91abc5;
        font-size: 3rem;
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

        .products-page .product-table thead th,
        .products-page .product-table tbody td {
            padding: .75rem;
        }

        .products-page .product-image {
            flex-basis: 44px;
            width: 44px;
            height: 44px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .products-page * {
            scroll-behavior: auto;
        }
    }
</style>
@endpush