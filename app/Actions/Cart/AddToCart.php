<?php

namespace App\Actions\Cart;

use App\Models\{Cart, CartItem, ProductVariant};
use App\Exceptions\InsufficientStockException;

class AddToCart
{
    public function execute(Cart $cart, int $variantId, int $quantity = 1): CartItem
    {
        $variant = ProductVariant::findOrFail($variantId);

        $item = $cart->items()->firstOrNew(['product_variant_id' => $variant->id]);
        $newQuantity = ($item->quantity ?? 0) + $quantity;

        if ($variant->stock < $newQuantity) {
            throw new InsufficientStockException($variant);
        }

        $item->quantity = $newQuantity;
        $item->save();

        return $item;
    }
}