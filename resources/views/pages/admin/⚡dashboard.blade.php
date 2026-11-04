
<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app-dashboard')]class extends Component
{
    //
};
?>

<div class="page-wrapper admin-dashboard">

    {{-- Encabezado de bienvenida --}}
    <div class="page-header d-print-none">
        <div class="container-fluid">
            <div class="row g-3 align-items-center">
                <div class="col">
                    <div class="page-pretitle text-uppercase">
                        Panel de administración
                    </div>

                    <h2 class="page-title">
                        ¡Bienvenido, {{ auth()->user()?->name ?? 'Administrador' }}!
                    </h2>

                    <div class="text-secondary mt-1">
                        Gestiona tu tienda de calzado y supervisa el estado
                        general de tu negocio desde un solo lugar.
                    </div>
                </div>

                <div class="col-auto ms-auto">
                    <div class="d-flex align-items-center gap-2">
                        <span class="avatar avatar-md rounded bg-blue-lt">
                            <i class="ti ti-dashboard fs-2"></i>
                        </span>
                        <div class="d-none d-sm-block">
                            <div class="fw-semibold">Panel principal</div>
                            <div class="text-secondary small">
                                Administración
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-fluid">

            {{-- Accesos rápidos --}}
            <div class="row row-cards mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ti ti-layout-grid me-2 text-blue"></i>
                                Accesos rápidos
                            </h3>
                            <div class="card-actions text-secondary small">
                                Gestión de la tienda
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row g-3">

                                {{-- Marcas --}}
                                <div class="col-12 col-sm-6 col-xl-3">
                                    <a href="{{ route('admin.brand.index') }}"
                                       class="dashboard-shortcut">
                                        <span class="shortcut-icon bg-azure-lt">
                                            <i class="ti ti-tags"></i>
                                        </span>
                                        <span class="shortcut-content">
                                            <span class="shortcut-title">
                                                Marcas
                                            </span>
                                            <span class="shortcut-description">
                                                Administra las marcas de calzado.
                                            </span>
                                        </span>
                                        <i class="ti ti-chevron-right shortcut-arrow"></i>
                                    </a>
                                </div>

                                {{-- Categorías --}}
                                <div class="col-12 col-sm-6 col-xl-3">
                                    <a href="{{ route('admin.categories-index') }}"
                                       class="dashboard-shortcut">
                                        <span class="shortcut-icon bg-indigo-lt">
                                            <i class="ti ti-category"></i>
                                        </span>
                                        <span class="shortcut-content">
                                            <span class="shortcut-title">
                                                Categorías
                                            </span>
                                            <span class="shortcut-description">
                                                Organiza tus tipos de calzado.
                                            </span>
                                        </span>
                                        <i class="ti ti-chevron-right shortcut-arrow"></i>
                                    </a>
                                </div>

                                {{-- Órdenes --}}
                                <div class="col-12 col-sm-6 col-xl-3">
                                    <a href="{{ route('admin.orders.index') }}"
                                       class="dashboard-shortcut">
                                        <span class="shortcut-icon bg-orange-lt">
                                            <i class="ti ti-shopping-cart"></i>
                                        </span>
                                        <span class="shortcut-content">
                                            <span class="shortcut-title">
                                                Órdenes
                                            </span>
                                            <span class="shortcut-description">
                                                Consulta y gestiona los pedidos.
                                            </span>
                                        </span>
                                        <i class="ti ti-chevron-right shortcut-arrow"></i>
                                    </a>
                                </div>

                                {{-- Productos --}}
                                <div class="col-12 col-sm-6 col-xl-3">
                                    <a href="{{ route('products.index') }}"
                                       class="dashboard-shortcut">
                                        <span class="shortcut-icon bg-green-lt">
                                            <i class="ti ti-shoe"></i>
                                        </span>
                                        <span class="shortcut-content">
                                            <span class="shortcut-title">
                                                Productos
                                            </span>
                                            <span class="shortcut-description">
                                                Gestiona el catálogo y stock.
                                            </span>
                                        </span>
                                        <i class="ti ti-chevron-right shortcut-arrow"></i>
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tarjetas estadísticas --}}
            <div class="row row-cards mb-4">

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card dashboard-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar rounded bg-blue-lt">
                                    <i class="ti ti-package fs-2"></i>
                                </span>
                                <div class="ms-auto">
                                    <span class="badge bg-blue-lt">
                                        Catálogo
                                    </span>
                                </div>
                            </div>

                            <div class="mt-3">
                                <div class="text-secondary">
                                    Total de productos
                                </div>
                                <div class="h1 mb-1">—</div>
                                <div class="text-secondary small">
                                    Productos registrados
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card dashboard-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar rounded bg-indigo-lt">
                                    <i class="ti ti-tags fs-2"></i>
                                </span>
                                <div class="ms-auto">
                                    <span class="badge bg-indigo-lt">
                                        Marcas
                                    </span>
                                </div>
                            </div>

                            <div class="mt-3">
                                <div class="text-secondary">
                                    Total de marcas
                                </div>
                                <div class="h1 mb-1">—</div>
                                <div class="text-secondary small">
                                    Marcas registradas
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card dashboard-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar rounded bg-orange-lt">
                                    <i class="ti ti-shopping-bag fs-2"></i>
                                </span>
                                <div class="ms-auto">
                                    <span class="badge bg-orange-lt">
                                        Pedidos
                                    </span>
                                </div>
                            </div>

                            <div class="mt-3">
                                <div class="text-secondary">
                                    Total de órdenes
                                </div>
                                <div class="h1 mb-1">—</div>
                                <div class="text-secondary small">
                                    Órdenes registradas
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card dashboard-stat-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <span class="avatar rounded bg-green-lt">
                                    <i class="ti ti-category fs-2"></i>
                                </span>
                                <div class="ms-auto">
                                    <span class="badge bg-green-lt">
                                        Organización
                                    </span>
                                </div>
                            </div>

                            <div class="mt-3">
                                <div class="text-secondary">
                                    Total de categorías
                                </div>
                                <div class="h1 mb-1">—</div>
                                <div class="text-secondary small">
                                    Categorías registradas
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Sección de gráficos --}}
            <div class="row align-items-center mb-3">
                <div class="col">
                    <h2 class="page-title fs-2">
                        Resumen y analítica
                    </h2>
                    <div class="text-secondary mt-1">
                        Visualiza los indicadores de tu tienda.
                    </div>
                </div>
            </div>

            <div class="row row-cards mb-4">

                {{-- Gráfico 1 --}}
                <div class="col-12 col-xl-6">
                    <div class="card dashboard-chart-card">
                        <div class="card-header">
                            <div>
                                <h3 class="card-title">
                                    Evolución de órdenes
                                </h3>
                                <div class="text-secondary small">
                                    Seguimiento de pedidos en el tiempo
                                </div>
                            </div>
                            <span class="avatar avatar-sm bg-blue-lt rounded">
                                <i class="ti ti-chart-line"></i>
                            </span>
                        </div>

                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="ordersChart"
                                        aria-label="Gráfico de evolución de órdenes"
                                        role="img"></canvas>
                                <div class="chart-empty-state">
                                    <i class="ti ti-chart-line"></i>
                                    <span>Gráfico pendiente de integración</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Gráfico 2 --}}
                <div class="col-12 col-xl-6">
                    <div class="card dashboard-chart-card">
                        <div class="card-header">
                            <div>
                                <h3 class="card-title">
                                    Distribución de productos
                                </h3>
                                <div class="text-secondary small">
                                    Productos por categoría
                                </div>
                            </div>
                            <span class="avatar avatar-sm bg-indigo-lt rounded">
                                <i class="ti ti-chart-donut"></i>
                            </span>
                        </div>

                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="productsCategoryChart"
                                        aria-label="Gráfico de productos por categoría"
                                        role="img"></canvas>
                                <div class="chart-empty-state">
                                    <i class="ti ti-chart-donut"></i>
                                    <span>Gráfico pendiente de integración</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Gráfico 3 --}}
                <div class="col-12 col-xl-6">
                    <div class="card dashboard-chart-card">
                        <div class="card-header">
                            <div>
                                <h3 class="card-title">
                                    Rendimiento de ventas
                                </h3>
                                <div class="text-secondary small">
                                    Evolución de ingresos por periodo
                                </div>
                            </div>
                            <span class="avatar avatar-sm bg-green-lt rounded">
                                <i class="ti ti-chart-bar"></i>
                            </span>
                        </div>

                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="salesChart"
                                        aria-label="Gráfico de rendimiento de ventas"
                                        role="img"></canvas>
                                <div class="chart-empty-state">
                                    <i class="ti ti-chart-bar"></i>
                                    <span>Gráfico pendiente de integración</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Gráfico 4 --}}
                <div class="col-12 col-xl-6">
                    <div class="card dashboard-chart-card">
                        <div class="card-header">
                            <div>
                                <h3 class="card-title">
                                    Marcas más representativas
                                </h3>
                                <div class="text-secondary small">
                                    Comparativa de productos por marca
                                </div>
                            </div>
                            <span class="avatar avatar-sm bg-orange-lt rounded">
                                <i class="ti ti-chart-pie"></i>
                            </span>
                        </div>

                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="brandsChart"
                                        aria-label="Gráfico de productos por marca"
                                        role="img"></canvas>
                                <div class="chart-empty-state">
                                    <i class="ti ti-chart-pie"></i>
                                    <span>Gráfico pendiente de integración</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Pie informativo --}}
            <div class="text-center text-secondary small py-3">
                Panel de administración · Gestión de tienda de calzado
            </div>

        </div>
    </div>
</div>

@push('styles')
<style>
    .admin-dashboard {
        --steel-50: #f7f9fc;
        --steel-100: #edf1f6;
        --steel-200: #dce3ec;
        --steel-300: #c4cfdd;
        --steel-500: #718096;
        --steel-700: #3d4d63;
        background: var(--steel-50);
        min-height: 100%;
    }

    .admin-dashboard .page-header {
        background: linear-gradient(
            135deg,
            #ffffff 0%,
            #f1f5fa 100%
        );
        border-bottom: 1px solid var(--steel-200);
        padding-top: 1.5rem;
        padding-bottom: 1.5rem;
    }

    .admin-dashboard .page-pretitle {
        color: var(--steel-500);
        letter-spacing: .09em;
        font-weight: 700;
    }

    .admin-dashboard .page-title {
        color: #26374a;
        font-weight: 700;
        letter-spacing: -.025em;
    }

    .admin-dashboard .card {
        border: 1px solid var(--steel-200);
        border-radius: 12px;
        box-shadow: 0 2px 8px rgb(37 55 77 / 4%);
        transition:
            box-shadow .2s ease,
            transform .2s ease,
            border-color .2s ease;
    }

    .admin-dashboard .card-header {
        padding: 1.15rem 1.25rem;
        border-bottom-color: var(--steel-100);
    }

    .admin-dashboard .card-title {
        color: #34465b;
        font-weight: 650;
    }

    .admin-dashboard .dashboard-shortcut {
        display: flex;
        align-items: center;
        gap: .85rem;
        height: 100%;
        min-height: 100px;
        padding: 1rem;
        color: inherit;
        text-decoration: none;
        background: #fff;
        border: 1px solid var(--steel-200);
        border-radius: 10px;
        transition: all .2s ease;
    }

    .admin-dashboard .dashboard-shortcut:hover {
        border-color: #9aafc7;
        background: #f8fafd;
        box-shadow: 0 5px 16px rgb(37 55 77 / 7%);
        transform: translateY(-2px);
    }

    .admin-dashboard .shortcut-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 46px;
        width: 46px;
        height: 46px;
        border-radius: 10px;
        font-size: 1.45rem;
    }

    .admin-dashboard .shortcut-content {
        display: flex;
        flex: 1;
        flex-direction: column;
        gap: .25rem;
        min-width: 0;
    }

    .admin-dashboard .shortcut-title {
        color: #34465b;
        font-weight: 650;
    }

    .admin-dashboard .shortcut-description {
        color: var(--steel-500);
        font-size: .8rem;
        line-height: 1.45;
    }

    .admin-dashboard .shortcut-arrow {
        color: #9aa8b9;
        flex-shrink: 0;
        font-size: 1.15rem;
    }

    .admin-dashboard .dashboard-stat-card {
        height: 100%;
    }

    .admin-dashboard .dashboard-stat-card:hover,
    .admin-dashboard .dashboard-chart-card:hover {
        border-color: var(--steel-300);
        box-shadow: 0 5px 18px rgb(37 55 77 / 6%);
    }

    .admin-dashboard .dashboard-stat-card .h1 {
        color: #293b50;
        font-size: clamp(1.65rem, 2vw, 2rem);
        font-weight: 700;
    }

    .admin-dashboard .chart-container {
        position: relative;
        width: 100%;
        height: 280px;
        overflow: hidden;
    }

    .admin-dashboard .chart-container canvas {
        position: relative;
        z-index: 1;
        width: 100% !important;
        height: 100% !important;
    }

    .admin-dashboard .chart-empty-state {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: .75rem;
        color: #8a9aaf;
        background:
            repeating-linear-gradient(
                to bottom,
                transparent 0,
                transparent 54px,
                #edf1f6 55px,
                transparent 56px
            );
        font-size: .85rem;
        pointer-events: none;
    }

    .admin-dashboard .chart-empty-state i {
        font-size: 2.4rem;
        color: #9cacc0;
    }

    @media (max-width: 575.98px) {
        .admin-dashboard .page-header {
            padding-top: 1rem;
            padding-bottom: 1rem;
        }

        .admin-dashboard .card-header {
            padding: 1rem;
        }

        .admin-dashboard .card-body {
            padding: 1rem;
        }

        .admin-dashboard .chart-container {
            height: 240px;
        }

        .admin-dashboard .dashboard-shortcut {
            min-height: 88px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .admin-dashboard .card,
        .admin-dashboard .dashboard-shortcut {
            transition: none;
        }
    }
</style>
@endpush
