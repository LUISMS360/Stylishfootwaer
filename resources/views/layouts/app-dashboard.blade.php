
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? config('app.name', 'Calzado Store') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles

    @stack('styles')
</head>

<body>
    <div class="page">

        {{-- Navbar principal --}}
        <header class="navbar navbar-expand-md navbar-light navbar-store">
            <div class="container-fluid">

                {{-- Logo --}}
                <a href="{{ route('admin.dashboard') }}" wire:navigate
                   class="navbar-brand d-flex align-items-center gap-2">
                    <span class="brand-icon">
                        <i class="ti ti-shoe"></i>
                    </span>

                    <span class="brand-name">
                        {{ config('app.name', 'Calzado Store') }}
                    </span>
                </a>

                {{-- Botón responsive --}}
                <button class="navbar-toggler"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#navbarAdmin"
                        aria-controls="navbarAdmin"
                        aria-expanded="false"
                        aria-label="Abrir menú de navegación">
                    <span class="navbar-toggler-icon"></span>
                </button>

                {{-- Navegación --}}
                <div class="collapse navbar-collapse" id="navbarAdmin">

                    <ul class="navbar-nav me-auto">

                        {{-- Marcas --}}
                        <li class="nav-item">
                            <a href="{{ route('admin.brand.index') }}" wire:navigate
                               @class([
                                   'nav-link',
                                   'active' => request()->is('admin/marcas*'),
                               ])
                               @if(request()->is('admin/marcas*')) aria-current="page" @endif>
                                <i class="ti ti-tags me-2"></i>
                                Marcas
                            </a>
                        </li>

                        {{-- Categorías --}}
                        <li class="nav-item">
                            <a href="{{ route('admin.categories-index') }}" wire:navigate
                               @class([
                                   'nav-link',
                                   'active' => request()->is('admin/categorias*'),
                               ])
                               @if(request()->is('admin/categorias*')) aria-current="page" @endif>
                                <i class="ti ti-category me-2"></i>
                                Categorías
                            </a>
                        </li>

                        {{-- Productos --}}
                        <li class="nav-item">
                            <a href="{{ route('admin.products-index') }}" wire:navigate
                               @class([
                                   'nav-link',
                                   'active' => request()->is('admin/products*'),
                               ])
                               @if(request()->is('admin/products*')) aria-current="page" @endif>
                                <i class="ti ti-package me-2"></i>
                                Productos
                            </a>
                        </li>

                        {{-- Órdenes --}}
                        <li class="nav-item">
                            <a href="{{ route('admin.orders.index') }}" wire:navigate
                               @class([
                                   'nav-link',
                                   'active' => request()->is('admin/ordenes*'),
                               ])
                               @if(request()->is('admin/ordenes*')) aria-current="page" @endif>
                                <i class="ti ti-shopping-cart me-2"></i>
                                Órdenes
                            </a>
                        </li>

                    </ul>

                    {{-- Perfil del administrador --}}
                    <div class="navbar-nav ms-auto">
                        @auth
                            <div class="nav-item dropdown">
                                <a href="#"
                                   class="nav-link dropdown-toggle d-flex align-items-center gap-2"
                                   data-bs-toggle="dropdown"
                                   aria-expanded="false">

                                    <span class="avatar avatar-sm rounded-circle admin-avatar">
                                        <i class="ti ti-user"></i>
                                    </span>

                                    <span class="d-none d-lg-inline">
                                        {{ auth()->user()->name }}
                                    </span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-end">
                                    <div class="dropdown-header">
                                        Cuenta de administrador
                                    </div>

                                    <div class="dropdown-divider"></div>

                                    {{-- Ajusta esta ruta a tu sistema de autenticación --}}
                                    <form method="POST"
                                          action="{{ route('logout') }}">
                                        @csrf

                                        <button type="submit"
                                                class="dropdown-item text-danger">
                                            <i class="ti ti-logout me-2"></i>
                                            Cerrar sesión
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endauth
                    </div>

                </div>
            </div>
        </header>

        {{-- Contenido de la página --}}
        <main class="page-wrapper">
            {{ $slot }}
        </main>

        {{-- Footer --}}
        <footer class="footer footer-transparent d-print-none">
            <div class="container-fluid">
                <div class="text-center text-secondary small py-2">
                    &copy; {{ date('Y') }}
                    {{ config('app.name', 'Calzado Store') }}.
                    Panel de administración.
                </div>
            </div>
        </footer>

    </div>

    @livewireScripts

    @stack('scripts')
</body>

</html>
