<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Brand::create([
            'name'=>'Adidas',
            'slug'=>'Adidas es una compañía multinacional alemana de origen deportivo fundada en 1949 por Adolf «Adi» Dassler, con sede en Herzogenaurach, Alemania. Es el segundo mayor fabricante de ropa y calzado deportivo del mundo'
        ]);
        Brand::create([
            'name'=>'Nike',
            'slug'=>'Nike, Inc. es una multinacional estadounidense dedicada al diseño, fabricación y comercialización de equipamiento deportivo.'
        ]);
        Brand::create([
            'name'=>'On',
            'slug'=>'Las zapatillas On Running son conocidas por su tecnología de amortiguación CloudTec, que utiliza elementos en la suela para suavizar el impacto y dar ligereza al caminar o correr'
        ]);
        Brand::create([
            'name'=>'Puma',
            'slug'=>'La historia de Puma SE comenzó a principios del siglo XX y está marcada por una rivalidad familiar legendaria que dio origen a dos gigantes del deporte mundial.'
        ]); 

        Category::create([
            'name'=>'Deportiva',
            'slug'=>' Tienen perfil bajo y suela plana (sin tacos) para mejorar el control del balón en superficies interiores.'
        ]);

        Category::create([
            'name'=>'Runing',
            'slug'=>'Tienen suelas con buena amortiguación y protección para acumular kilómetros de forma segura.'
        ]);

        Category::create([
            'name'=>'Caminar',
            'slug'=>' Orientadas al tipo de pisada y control de la pronación del corredor.'
        ]);
        Category::create([
            'name'=>'Urbanas',
            'slug'=>'Diseñadas para el uso diario, priorizando la estética y la comodidad cotidiana.'
        ]);

        // $sizes = [30,31,32,33,34,35,36,37,38,39,40];
        // for ($i = 84; $i <= 106; $i++) {        
        //     for ($j = 0; $j <= 10; $j++) {
        //         ProductVariant::create([
        //             'product_id' => $i,
        //             'size'=>$sizes[$j],
        //             'color'=>'Referente al producto',
        //             'stock'=>100,
        //             'sku'=>'COD-'.$i.'-PRODUCT'
        //         ]);
        //     }
        // }
    }
}
