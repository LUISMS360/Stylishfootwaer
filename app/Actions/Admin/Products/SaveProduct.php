<?php

namespace App\Actions\Admin\Products;

use App\Models\Product;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SaveProduct
{
    public function execute(array $dataProduct): array     
{
    try {
        DB::beginTransaction();

        // 1. Guardar la imagen física en storage/app/public/products
        if (isset($dataProduct['image']) && $dataProduct['image'] instanceof \Illuminate\Http\UploadedFile) {
            $pathImage = Storage::disk('public')->putFile('products', $dataProduct['image']);
            
            // 2. Sobrescribir el atributo con la ruta en texto
            $dataProduct['image'] = $pathImage;
        }

        // 3. Insertar el producto en la base de datos
        $product = Product::create($dataProduct);

        DB::commit();

        return [
            'status'  => true,
            'message' => 'Producto creado correctamente!',
            'data'    => $product,
        ];

    } catch (Throwable $ex) {
        DB::rollBack();

        // Si la imagen alcanzó a subirse antes del error en BD, la eliminamos para no dejar basura
        if (isset($pathImage) && Storage::disk('public')->exists($pathImage)) {
            Storage::disk('public')->delete($pathImage);
        }

        return [
            'status'  => false,
            'message' => 'Ha ocurrido un error al crear el producto: ' . $ex->getMessage(),
        ];
    }
}
}
