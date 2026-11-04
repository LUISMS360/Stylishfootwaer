
<?php

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app-dashboard')] class extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    /**
     * Obtener órdenes con su cliente y sus artículos.
     */
    #[Computed]
    public function orders()
    {
        return Order::query()
            ->with('user')
            ->withCount('items')
            ->when(trim($this->search) !== '', function ($query) {
                $search = trim($this->search);

                $query->where(function ($query) use ($search) {
                    // Buscar por número de orden.
                    if (ctype_digit($search)) {
                        $query->orWhere('id', (int) $search);
                    }

                    // Buscar por dirección de envío.
                    $query->orWhere(
                        'shipping_address',
                        'like',
                        "%{$search}%"
                    );

                    // Buscar por estado.
                    $query->orWhere(
                        'status',
                        'like',
                        "%{$search}%"
                    );

                    // Buscar por datos del cliente.
                    $query->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
    }

    /**
     * Cantidad total de órdenes registradas.
     */
    #[Computed]
    public function totalOrders(): int
    {
        return Order::count();
    }

    /**
     * Cantidad de órdenes por estado.
     */
    #[Computed]
    public function countByStatus(string $status): int
    {
        return Order::where('status', $status)->count();
    }

    /**
     * Estados permitidos y sus etiquetas.
     */
    public function statusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Pendiente',
            'paid' => 'Pagada',
            'shipped' => 'Enviada',
            'delivered' => 'Entregada',
            'cancelled' => 'Cancelada',
            default => ucfirst($status),
        };
    }

    /**
     * Color de cada estado en Tabler UI.
     */
    public function statusClass(string $status): string
    {
        return match ($status) {
            'pending' => 'bg-warning-lt text-warning',
            'paid' => 'bg-success-lt text-success',
            'shipped' => 'bg-info-lt text-info',
            'delivered' => 'bg-primary-lt text-primary',
            'cancelled' => 'bg-danger-lt text-danger',
            default => 'bg-secondary-lt text-secondary',
        };
    }

    /**
     * Ordenar la tabla por una columna permitida.
     */
    public function sortBy(string $field): void
    {
        if (! in_array($field, ['id', 'total', 'status', 'created_at'], true)) {
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

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    /**
     * Eliminar una orden y sus artículos asociados.
     */
    public function deleteOrder(int $orderId): void
    {
        // La autorización de administrador también debe configurarse
        // mediante middleware y/o una política de Laravel.
        abort_unless(
            auth()->check() && auth()->user()->role === 'administrator',
            403
        );

        $order = Order::findOrFail($orderId);

        DB::transaction(function () use ($order) {
            // Evitar registros de artículos huérfanos si no existe
            // eliminación en cascada configurada en la base de datos.
            $order->orderItem()->delete();

            $order->delete();
        });

        unset($this->orders, $this->totalOrders);

        $this->dispatch('order-deleted');

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
                    <div class="page-pretitle">Administración</div>

                    <h2 class="page-title">
                        <i class="ti ti-shopping-cart me-2"></i>
                        Gestión de órdenes
                    </h2>

                    <div class="text-secondary mt-1">
                        Consulta los pedidos, sus estados y los importes registrados.
                    </div>
                </div>
            </div>
        </div>

        {{-- Resumen de estados --}}
        <div class="row row-cards mb-4">

            <div class="col-6 col-md">
                <div class="card">
                    <div class="card-body">
                        <div class="text-secondary">Total de órdenes</div>
                        <div class="h1 mb-0">{{ $this->totalOrders }}</div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md">
                <div class="card">
                    <div class="card-body">
                        <div class="text-secondary">Pendientes</div>
                        <div class="h1 mb-0 text-warning">
                            {{ $this->countByStatus('pending') }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md">
                <div class="card">
                    <div class="card-body">
                        <div class="text-secondary">Pagadas</div>
                        <div class="h1 mb-0 text-success">
                            {{ $this->countByStatus('paid') }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md">
                <div class="card">
                    <div class="card-body">
                        <div class="text-secondary">Enviadas</div>
                        <div class="h1 mb-0 text-info">
                            {{ $this->countByStatus('shipped') }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md">
                <div class="card">
                    <div class="card-body">
                        <div class="text-secondary">Entregadas</div>
                        <div class="h1 mb-0 text-primary">
                            {{ $this->countByStatus('delivered') }}
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Tabla principal --}}
        <div class="card">

            <div class="card-header">
                <h3 class="card-title">Listado de órdenes</h3>

                <div class="card-actions">
                    <span class="badge bg-primary-lt">
                        {{ $this->orders->total() }} resultados
                    </span>
                </div>
            </div>

            {{-- Buscador --}}
            <div class="card-body border-bottom py-3">
                <div class="row g-2 align-items-center">

                    <div class="col-12 col-md-7 col-lg-5">
                        <div class="input-icon">
                            <span class="input-icon-addon">
                                <i class="ti ti-search"></i>
                            </span>

                            <input
                                type="search"
                                class="form-control"
                                placeholder="Buscar por orden, cliente, correo, estado o dirección..."
                                aria-label="Buscar órdenes"
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
                        Mostrando {{ $this->orders->firstItem() ?? 0 }}
                        a {{ $this->orders->lastItem() ?? 0 }}
                        de {{ $this->orders->total() }} órdenes
                    </div>

                </div>
            </div>

            {{-- Tabla --}}
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-hover">

                    <thead>
                        <tr>
                            <th>
                                <button
                                    type="button"
                                    class="table-sort text-reset border-0 bg-transparent p-0"
                                    wire:click="sortBy('id')"
                                >
                                    Orden #

                                    @if ($sortField === 'id')
                                        <i class="ti ti-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </button>
                            </th>

                            <th>Cliente</th>

                            <th>Productos</th>

                            <th>
                                <button
                                    type="button"
                                    class="table-sort text-reset border-0 bg-transparent p-0"
                                    wire:click="sortBy('status')"
                                >
                                    Estado

                                    @if ($sortField === 'status')
                                        <i class="ti ti-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </button>
                            </th>

                            <th>
                                <button
                                    type="button"
                                    class="table-sort text-reset border-0 bg-transparent p-0"
                                    wire:click="sortBy('created_at')"
                                >
                                    Fecha

                                    @if ($sortField === 'created_at')
                                        <i class="ti ti-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </button>
                            </th>

                            <th class="text-end">
                                <button
                                    type="button"
                                    class="table-sort text-reset border-0 bg-transparent p-0"
                                    wire:click="sortBy('total')"
                                >
                                    Total

                                    @if ($sortField === 'total')
                                        <i class="ti ti-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </button>
                            </th>

                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($this->orders as $order)
                            <tr wire:key="order-{{ $order->id }}">

                                <td>
                                    <span class="fw-bold">
                                        #{{ $order->id }}
                                    </span>
                                </td>

                                <td>
                                    <div class="fw-medium">
                                        {{ $order->user?->name ?? 'Usuario eliminado' }}
                                    </div>

                                    <div class="text-secondary small">
                                        {{ $order->user?->email ?? 'Sin correo' }}
                                    </div>
                                </td>

                                <td>
                                    <span class="badge bg-azure-lt">
                                        {{ $order->order_item_count }}
                                        {{ $order->order_item_count === 1 ? 'artículo' : 'artículos' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="badge {{ $this->statusClass($order->status) }}">
                                        <span class="status-dot me-1"></span>
                                        {{ $this->statusLabel($order->status) }}
                                    </span>
                                </td>

                                <td class="text-nowrap">
                                    {{ $order->created_at?->format('d/m/Y') ?? '—' }}

                                    <div class="text-secondary small">
                                        {{ $order->created_at?->format('h:i A') }}
                                    </div>
                                </td>

                                <td class="text-end text-nowrap fw-bold">
                                    S/ {{ number_format((float) $order->total, 2) }}
                                </td>

                                <td class="text-end">
                                    <button
                                        type="button"
                                        class="btn btn-icon btn-outline-danger"
                                        title="Eliminar orden"
                                        aria-label="Eliminar orden {{ $order->id }}"
                                        wire:click="deleteOrder({{ $order->id }})"
                                        wire:confirm="¿Estás seguro de eliminar la orden #{{ $order->id }} y todos sus artículos? Esta acción no se puede deshacer."
                                        wire:loading.attr="disabled"
                                        wire:target="deleteOrder({{ $order->id }})"
                                    >
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty py-5">
                                        <div class="empty-img">
                                            <i
                                                class="ti ti-shopping-cart-off"
                                                style="font-size: 4rem;"
                                            ></i>
                                        </div>

                                        <p class="empty-title">
                                            No se encontraron órdenes
                                        </p>

                                        <p class="empty-subtitle text-secondary">
                                            @if (trim($search) !== '')
                                                Prueba con otro término de búsqueda.
                                            @else
                                                Todavía no hay órdenes registradas.
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
            @if ($this->orders->hasPages())
                <div class="card-footer d-flex align-items-center">
                    <p class="m-0 text-secondary">
                        Página {{ $this->orders->currentPage() }}
                        de {{ $this->orders->lastPage() }}
                    </p>

                    <div class="ms-auto">
                        {{ $this->orders->links() }}
                    </div>
                </div>
            @endif

            {{-- Indicador de carga --}}
            <div
                wire:loading.flex
                wire:target="search,sortBy,clearSearch,gotoPage,nextPage,previousPage,deleteOrder"
                class="card-footer text-secondary small align-items-center gap-2"
            >
                <span class="spinner-border spinner-border-sm" role="status"></span>
                Procesando solicitud...
            </div>

        </div>
    </div>

    {{-- Notificación de eliminación --}}
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('order-deleted', () => {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Orden eliminada',
                        text: 'La orden y sus artículos se eliminaron correctamente.',
                        confirmButtonText: 'Aceptar'
                    });
                }
            });
        });
    </script>
</div>
