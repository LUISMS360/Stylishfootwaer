
<?php

use App\Models\Brand;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    /**
     * Obtener las marcas con búsqueda, ordenamiento y paginación.
     */
    #[Computed]
    public function brands()
    {
        return Brand::query()
            ->withCount('product')
            ->when(trim($this->search) !== '', function ($query) {
                $search = trim($this->search);

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
    }

    /**
     * Reiniciar la paginación al buscar.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Ordenar por una columna permitida.
     */
    public function sortBy(string $field): void
    {
        if (! in_array($field, ['name', 'slug', 'created_at'], true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection =
                $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    /**
     * Limpiar el buscador.
     */
    public function clearSearch(): void
    {
        $this->search = '';

        $this->resetPage();
    }
};
?>

<div>
    <div class="container-xl py-4">

        {{-- Encabezado --}}
        <div class="page-header d-print-none mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <div class="page-pretitle">
                        Administración
                    </div>

                    <h2 class="page-title">
                        <i class="ti ti-tags me-2"></i>
                        Marcas
                    </h2>

                    <div class="text-secondary mt-1">
                        Consulta y administra las marcas de tus productos.
                    </div>
                </div>

                <div class="col-auto ms-auto">
                    {{-- Se habilitará al implementar el formulario --}}
                    <button type="button" class="btn btn-primary" disabled>
                        <i class="ti ti-plus me-2"></i>
                        Nueva marca
                    </button>
                </div>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="card">

            <div class="card-header">
                <h3 class="card-title">
                    Listado de marcas
                </h3>

                <div class="card-actions">
                    <span class="badge bg-primary-lt">
                        {{ $this->brands->total() }} registros
                    </span>
                </div>
            </div>

            {{-- Buscador --}}
            <div class="card-body border-bottom py-3">
                <div class="row g-2 align-items-center">

                    <div class="col-12 col-md-6 col-lg-5">
                        <div class="input-icon">
                            <span class="input-icon-addon">
                                <i class="ti ti-search"></i>
                            </span>

                            <input
                                type="search"
                                class="form-control"
                                placeholder="Buscar por nombre o slug..."
                                aria-label="Buscar marcas"
                                wire:model.live.debounce.350ms="search"
                            >
                        </div>
                    </div>

                    <div class="col-auto">
                        @if ($search !== '')
                            <button
                                type="button"
                                class="btn btn-outline-secondary"
                                wire:click="clearSearch"
                            >
                                <i class="ti ti-x me-1"></i>
                                Limpiar
                            </button>
                        @endif
                    </div>

                    <div class="col-12 col-md-auto ms-md-auto text-secondary small">
                        Mostrando {{ $this->brands->firstItem() ?? 0 }}
                        a {{ $this->brands->lastItem() ?? 0 }}
                        de {{ $this->brands->total() }} marcas
                    </div>

                </div>
            </div>

            {{-- Contenido --}}
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-hover">

                    <thead>
                        <tr>
                            <th style="width: 80px;">
                                ID
                            </th>

                            <th>
                                <button
                                    type="button"
                                    class="table-sort text-reset border-0 bg-transparent p-0"
                                    wire:click="sortBy('name')"
                                >
                                    Nombre

                                    @if ($sortField === 'name')
                                        <i class="ti ti-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </button>
                            </th>

                            <th>
                                <button
                                    type="button"
                                    class="table-sort text-reset border-0 bg-transparent p-0"
                                    wire:click="sortBy('slug')"
                                >
                                    Slug

                                    @if ($sortField === 'slug')
                                        <i class="ti ti-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </button>
                            </th>

                            <th class="text-center">
                                Productos
                            </th>

                            <th class="text-end">
                                Fecha de creación
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($this->brands as $brand)
                            <tr wire:key="brand-{{ $brand->id }}">

                                <td class="text-secondary">
                                    {{ $brand->id }}
                                </td>

                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="avatar avatar-sm bg-purple-lt">
                                            <i class="ti ti-tag"></i>
                                        </span>

                                        <span class="fw-medium">
                                            {{ $brand->name }}
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <code>{{ $brand->slug }}</code>
                                </td>

                                <td class="text-center">
                                    <span class="badge bg-azure-lt">
                                        {{ $brand->product_count }}
                                    </span>
                                </td>

                                <td class="text-end text-secondary text-nowrap">
                                    {{ $brand->created_at?->format('d/m/Y') ?? '—' }}
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="empty py-5">
                                        <div class="empty-img">
                                            <i class="ti ti-tags-off"
                                               style="font-size: 4rem;"></i>
                                        </div>

                                        <p class="empty-title">
                                            No se encontraron marcas
                                        </p>

                                        <p class="empty-subtitle text-secondary">
                                            @if (trim($search) !== '')
                                                Intenta buscar con otro nombre o slug.
                                            @else
                                                Todavía no hay marcas registradas.
                                            @endif
                                        </p>

                                        @if ($search !== '')
                                            <div class="empty-action">
                                                <button
                                                    type="button"
                                                    class="btn btn-outline-secondary"
                                                    wire:click="clearSearch"
                                                >
                                                    Limpiar búsqueda
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

            {{-- Paginación --}}
            @if ($this->brands->hasPages())
                <div class="card-footer d-flex align-items-center">
                    <p class="m-0 text-secondary">
                        Página {{ $this->brands->currentPage() }}
                        de {{ $this->brands->lastPage() }}
                    </p>

                    <div class="ms-auto">
                        {{ $this->brands->links() }}
                    </div>
                </div>
            @endif

            {{-- Indicador de carga --}}
            <div
                wire:loading.flex
                wire:target="search,sortBy,clearSearch,gotoPage,nextPage,previousPage"
                class="card-footer text-secondary small align-items-center gap-2"
            >
                <span class="spinner-border spinner-border-sm" role="status"></span>
                Actualizando marcas...
            </div>

        </div>
    </div>
</div>
