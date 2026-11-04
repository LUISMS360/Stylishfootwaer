<x-layouts.app>

       <a href="{{ route('products.index') }}" wire:navigate class="btn btn-danger">Productos</a>
       <a href="{{ route('products.create') }}" wire:navigate class="btn btn-primary">Crear Producto</a>
       <a href="{{ route('admin.dashboard') }}" wire:navigate class="btn btn-primary">Dashboard Admin</a>
</x-layouts.app>