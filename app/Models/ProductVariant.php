<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['product_id','size','color','stock','sku'])]
class ProductVariant extends Model
{
    /** @use HasFactory<\Database\Factories\ProductVariantFactory> */
    use HasFactory;

    public function orderItem(){
        return $this->hasMany(OrderItem::class);
    }

    public function product(){
        return  $this->belongsTo(Product::class);
    }
    
}
