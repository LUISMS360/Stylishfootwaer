
<?php

use App\Models\Order;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    /**
     * Obtener todas las órdenes del usuario autenticado.
     */
    #[Computed]
    public function orders()
    {
        $user = auth()->user();

        if (! $user) {
            return collect();
        }

        return Order::query()
            ->where('user_id', $user->id)
            ->with(['orderItem.productVariant'])
            ->latest()
            ->get();
    }

    /**
     * Cantidad total de órdenes.
     */
    #[Computed]
    public function totalOrders(): int
    {
        return $this->orders->count();
    }

    /**
     * Suma del importe de todas las órdenes.
     */
    #[Computed]
    public function totalAmount(): float
    {
        return (float) $this->orders->sum('total');
    }

    /**
     * Cantidad de órdenes por estado.
     */
    #[Computed]
    public function countByStatus(string $status): int
    {
        return $this->orders
            ->where('status', $status)
            ->count();
    }

    /**
     * Clases visuales de Tabler según el estado.
     */
    public function statusClass(string $status): string
    {
        return match (strtolower($status)) {
            'completed', 'completado', 'entregado' => 'bg-success-lt',
            'pending', 'pendiente' => 'bg-warning-lt',
            'cancelled', 'cancelado' => 'bg-danger-lt',
            'processing', 'procesando', 'enviado' => 'bg-info-lt',
            default => 'bg-secondary-lt',
        };
    }

    /**
     * Etiqueta legible para el estado.
     */
    public function statusLabel(string $status): string
    {
        return match (strtolower($status)) {
            'completed' => 'Completada',
            'pending' => 'Pendiente',
            'cancelled' => 'Cancelada',
            'processing' => 'En proceso',
            default => ucfirst($status),
        };
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
                        Mi cuenta
                    </div>

                    <h2 class="page-title">
                        <i class="ti ti-package me-2"></i>
                        Mis órdenes
                    </h2>

                    <div class="text-secondary mt-1">
                        Consulta tus compras y revisa el estado de tus pedidos.
                    </div>
                </div>
            </div>
        </div>

        {{-- Resumen --}}
        <div class="row row-cards mb-4">

            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <span class="avatar bg-primary-lt me-3">
                                <i class="ti ti-shopping-bag fs-2"></i>
                            </span>

                            <div>
                                <div class="text-secondary">
                                    Total de órdenes
                                </div>

                                <div class="h1 mb-0">
                                    {{ $this->totalOrders }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <span class="avatar bg-success-lt me-3">
                                <i class="ti ti-currency-sol fs-2"></i>
                            </span>

                            <div>
                                <div class="text-secondary">
                                    Importe acumulado
                                </div>

                                <div class="h1 mb-0">
                                    S/ {{ number_format($this->totalAmount, 2) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <span class="avatar bg-warning-lt me-3">
                                <i class="ti ti-clock-hour-4 fs-2"></i>
                            </span>

                            <div>
                                <div class="text-secondary">
                                    Órdenes pendientes
                                </div>

                                <div class="h1 mb-0">
                                    {{ $this->countByStatus('pending') + $this->countByStatus('pendiente') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Listado de órdenes --}}
        <div class="card">

            <div class="card-header">
                <h3 class="card-title">
                    Historial de compras
                </h3>

                <div class="card-actions">
                    <span class="text-secondary small">
                        {{ $this->totalOrders }} registros
                    </span>
                </div>
            </div>

            @if ($this->orders->isEmpty())

                <div class="card-body">
                    <div class="empty py-5">
                        <div class="empty-img">
                            <i class="ti ti-package-off" style="font-size: 5rem;"></i>
                        </div>

                        <p class="empty-title">
                            Todavía no tienes órdenes
                        </p>

                        <p class="empty-subtitle text-secondary">
                            Cuando realices una compra, podrás consultar aquí los detalles.
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

            @else

                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>N.º orden</th>
                                <th>Fecha</th>
                                <th>Productos</th>
                                <th>Dirección de envío</th>
                                <th>Estado</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($this->orders as $order)
                                <tr wire:key="order-{{ $order->id }}">

                                    <td>
                                        <span class="fw-bold">
                                            #{{ $order->id }}
                                        </span>
                                    </td>

                                    <td class="text-nowrap">
                                        {{ $order->created_at?->format('d/m/Y') ?? '—' }}

                                        <div class="text-secondary small">
                                            {{ $order->created_at?->format('h:i A') }}
                                        </div>
                                    </td>

                                    <td>
                                        <span class="badge bg-primary-lt">
                                            {{ $order->orderItem->sum('quantity') }}
                                            unidades
                                        </span>
                                    </td>

                                    <td>
                                        <div class="text-wrap" style="min-width: 150px;">
                                            {{ $order->shipping_address ?: 'No especificada' }}
                                        </div>
                                    </td>

                                    <td>
                                        <span class="badge {{ $this->statusClass($order->status) }}">
                                            {{ $this->statusLabel($order->status) }}
                                        </span>
                                    </td>

                                    <td class="text-end text-nowrap">
                                        <span class="fw-bold">
                                            S/ {{ number_format((float) $order->total, 2) }}
                                        </span>
                                    </td>

                                </tr>
                            @endforeach
                        </tbody>

                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-end fw-bold">
                                    Total acumulado
                                </td>

                                <td class="text-end text-primary fw-bold text-nowrap">
                                    S/ {{ number_format($this->totalAmount, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            @endif
        </div>

    </div>
</div>
