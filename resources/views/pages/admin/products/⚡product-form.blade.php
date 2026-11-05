<?php

use App\Actions\Admin\Products\SaveProduct;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app')] class extends Component
{
    use WithFileUploads;

    #[Validate('required|string|max:255')]
    public $name = '';

    #[Validate('required|string|max:255')]
    public $slug = '';

    #[Validate('required|integer|exists:categories,id')]
    public $category_id = '';

    #[Validate('required|integer|exists:brands,id')]
    public $brand_id = '';

    #[Validate('required|string|max:1000')]
    public $description = '';

    #[Validate('required|numeric|min:1|max:1000')]
    public $price = '';

    #[Validate('required|image|max:2048')]
    public $image = null;

    #[Validate('boolean')]
    public $state = true;

    public function save(SaveProduct $action): void
    {
        // Las reglas de validación se ejecutan aquí.
        $this->validate();

        $productData = [
            'name' => $this->name,
            'slug' => $this->slug,
            'category_id' => $this->category_id,
            'brand_id' => $this->brand_id,
            'description' => $this->description,
            'price' => $this->price,
            'image' => $this->image,
            'is_active' => $this->state,
        ];

        // dd($productData);
        try {
            $result = $action->execute($productData);

            if (!$result['status']) {
                $this->dispatch(
                    'product-created-failed',
                    message: $result['message'] ?? 'No se pudo crear el producto.'
                );

                return;
            }

            $this->dispatch(
                'product-created-success',
                message: $result['message'] ?? 'Producto creado correctamente.'
            );

            return;
        } catch (\Throwable $exception) {
            Log::error('Error al crear un producto', [
                'exception' => $exception,
            ]);

            $this->dispatch(
                'product-created-failed',
                message: 'No se pudo crear el producto. Revisa los registros del servidor.'
            );
        }
    }

    #[Computed()]
    public function categories()
    {
        return Category::select('id', 'name')->get();
    }

    #[Computed()]
    public function brands()
    {
        return Brand::select('id', 'name')->get();
    }
};
?>

<div class="page-wrapper card m-3">
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">
                        Catálogo
                    </div>

                    <h2 class="page-title">
                        Crear producto
                    </h2>

                    <div class="text-secondary mt-1">
                        Registra un nuevo producto en el catálogo.
                    </div>
                </div>

                <div class="col-auto ms-auto">
                    <a href="{{ route('products.index') }}"
                        class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left me-2"></i>
                        Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">


            <form wire:submit="save" enctype="multipart/form-data">
                <div class="row row-cards">

                    {{-- Información del producto --}}
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="ti ti-package me-2"></i>
                                    Información del producto
                                </h3>
                            </div>

                            <div class="card-body">
                                <div class="row g-3">

                                    {{-- Nombre --}}
                                    <div class="col-12">
                                        <label class="form-label required">
                                            Nombre del producto
                                        </label>

                                        <input
                                            type="text"
                                            wire:model="name"
                                            class="form-control @error('name') is-invalid @enderror"
                                            placeholder="Ej. Teclado mecánico RGB">

                                        @error('name')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>

                                    {{-- slug --}}
                                    <div class="col-12">
                                        <label class="form-label required">
                                            Slug
                                        </label>

                                        <input
                                            type="text"
                                            wire:model="slug"
                                            class="form-control @error('slug') is-invalid @enderror"
                                            placeholder="">

                                        @error('slug')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>

                                    {{-- Categoría --}}
                                    <div class="col-md-6">
                                        <label class="form-label required">
                                            Categoría
                                        </label>

                                        <select
                                            wire:model="category_id"
                                            class="form-select @error('category_id') is-invalid @enderror">
                                            <option value="">
                                                Selecciona una categoría
                                            </option>

                                            @foreach ($this->categories() as $item)
                                            <option value="{{ $item->id }}">
                                                {{ $item->name }}
                                            </option>
                                            @endforeach
                                        </select>

                                        @error('category_id')
                                        <div class="text-danger mt-3">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>

                                    {{-- Marca --}}
                                    <div class="col-md-6">
                                        <label class="form-label required">
                                            Marca
                                        </label>

                                        <select
                                            wire:model="brand_id"
                                            class="form-select @error('brand_id') is-invalid @enderror">
                                            <option value="">
                                                Selecciona una marca
                                            </option>

                                            @foreach ($this->brands() as $item)
                                            <option value="{{ $item->id }}">
                                                {{ $item->name }}
                                            </option>
                                            @endforeach
                                        </select>

                                        @error('brand_id')
                                        <div class="text-danger mt-3">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>

                                    {{-- Descripción --}}
                                    <div class="col-12">
                                        <label class="form-label required">
                                            Descripción
                                        </label>

                                        <textarea
                                            wire:model="description"
                                            rows="5"
                                            maxlength="1000"
                                            class="form-control @error('description') is-invalid @enderror"
                                            placeholder="Describe las características del producto..."></textarea>

                                        <div class="form-hint">
                                            Máximo 1000 caracteres.
                                        </div>

                                        @error('description')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>

                                    {{-- Precio --}}
                                    <div class="col-md-6">
                                        <label class="form-label required">
                                            Precio
                                        </label>

                                        <div class="input-group">
                                            <span class="input-group-text">
                                                S/
                                            </span>

                                            <input
                                                type="number"
                                                wire:model="price"
                                                min="1"
                                                step="0.01"
                                                class="form-control @error('price') is-invalid @enderror"
                                                placeholder="0.00">
                                        </div>

                                        @error('price')
                                        <div class="text-danger small mt-1">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Imagen y estado --}}
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="ti ti-photo me-2"></i>
                                    Imagen del producto
                                </h3>
                            </div>

                            <div class="card-body">

                                <label class="form-label required">
                                    Seleccionar imagen
                                </label>

                                <input
                                    type="file"
                                    wire:model="image"
                                    accept=".png,.jpg,.jpeg,image/png,image/jpeg"
                                    class="form-control @error('image') is-invalid @enderror">

                                <div class="form-hint">
                                    Formatos: JPG, JPEG o PNG. Máximo 2 MB.
                                </div>

                                @error('image')
                                <div class="text-danger small mt-2">
                                    {{ $message }}
                                </div>
                                @enderror

                                <div wire:loading wire:target="image"
                                    class="text-primary small mt-2">
                                    <span class="spinner-border spinner-border-sm me-1"></span>
                                    Cargando imagen...
                                </div>

                                <div class="mt-3">
                                    @if ($image)
                                    <div class="border rounded p-2 text-center">
                                        <img
                                            src="{{ $image->temporaryUrl() }}"
                                            alt="Vista previa del producto"
                                            class="img-fluid rounded"
                                            style="max-height: 240px; object-fit: contain;">
                                    </div>
                                    @else
                                    <div class="border border-dashed rounded text-center p-5">
                                        <i class="ti ti-photo-off text-secondary"
                                            style="font-size: 3rem;"></i>

                                        <div class="text-secondary mt-2">
                                            La vista previa aparecerá aquí.
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="ti ti-settings me-2"></i>
                                    Configuración
                                </h3>
                            </div>

                            <div class="card-body">
                                <label class="form-label">
                                    Estado del producto
                                </label>

                                <label class="form-check form-switch">
                                    <input
                                        type="checkbox"
                                        wire:model="state"
                                        class="form-check-input">

                                    <span class="form-check-label">
                                        {{ $state ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </label>

                                <div class="form-hint">
                                    Los productos activos estarán disponibles
                                    según las reglas de tu catálogo.
                                </div>

                                @error('state')
                                <div class="text-danger small mt-2">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Acciones --}}
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body d-flex justify-content-end gap-2">
                                <a
                                    href="{{ route('products.index') }}"
                                    class="btn btn-outline-secondary"
                                    wire:loading.attr="disabled">
                                    Cancelar
                                </a>

                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                    wire:loading.attr="disabled"
                                    wire:target="save,image">
                                    <span wire:loading.remove wire:target="save">
                                        <i class="ti ti-device-floppy me-2"></i>
                                        Guardar producto
                                    </span>

                                    <span wire:loading wire:target="save">
                                        <span class="spinner-border spinner-border-sm me-2"></span>
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

@script
<script>
    // Producto creado correctamente
    $wire.on('product-created-success', (event) => {
        window.Swal.fire({
            icon: 'success',
            title: 'Producto creado',
            text: event.message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true
        });
    });

    // Error al crear el producto
    $wire.on('product-created-failed', (event) => {
        window.Swal.fire({
            icon: 'error',
            title: 'No se pudo crear el producto',
            text: event.message,
            confirmButtonText: 'Entendido'
        });
    });
</script>
@endscript