<?php

namespace App\Actions\Checkout;

use App\Enums\OrderStatus;
use App\Exceptions\InsufficientStockException;
use App\Jobs\SendOrderConfirmation;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PlaceOrder
{
    public function execute(
        User $user,
        string $shippingAddress,
        array $selectedVariants
    ): Order {
        $cart = session()->get('shopping_cart', []);

        if (! is_array($cart) || empty($cart)) {
            throw ValidationException::withMessages([
                'checkout' => 'Tu carrito está vacío.',
            ]);
        }

        // La compra completa debe confirmarse o revertirse.
        $order = DB::transaction(function () use (
            $user,
            $shippingAddress,
            $cart,
            $selectedVariants
        ) {
            $order = Order::create([
                'user_id' => $user->id,
                'total' => 0,
                'status' => OrderStatus::Pending,
                'shipping_address' => $shippingAddress,
            ]);

            $total = 0;

            foreach ($cart as $productId => $quantity) {
                $productId = (int) $productId;
                $quantity = (int) $quantity;

                if ($productId < 1 || $quantity < 1) {
                    throw ValidationException::withMessages([
                        'checkout' => 'El carrito contiene productos o cantidades inválidas.',
                    ]);
                }

                $variantId = $selectedVariants[$productId] ?? null;

                if (! $variantId) {
                    throw ValidationException::withMessages([
                        "selectedVariants.{$productId}" =>
                            'Debes seleccionar una talla y un color para cada producto.',
                    ]);
                }

                // Bloquea la variante mientras se comprueba y actualiza
                // el inventario. También verifica que pertenezca al producto.
                $variant = ProductVariant::query()
                    ->whereKey($variantId)
                    ->where('product_id', $productId)
                    ->lockForUpdate()
                    ->first();

                if (! $variant) {
                    throw ValidationException::withMessages([
                        "selectedVariants.{$productId}" =>
                            'La variante seleccionada no es válida.',
                    ]);
                }

                $variant->load('product');

                if (! $variant->product || ! $variant->product->is_active) {
                    throw ValidationException::withMessages([
                        'checkout' =>
                            'Uno de los productos ya no está disponible.',
                    ]);
                }

                if ($variant->stock < $quantity) {
                    throw new InsufficientStockException($variant);
                }

                $unitPrice = (float) $variant->product->price;
                $subtotal = $unitPrice * $quantity;

                $order->items()->create([
                    'product_variant_id' => $variant->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                ]);

                $variant->decrement('stock', $quantity);

                $total += $subtotal;
            }

            $order->update([
                'total' => $total,
            ]);

            // El Job se despachará después de confirmar la transacción.
            SendOrderConfirmation::dispatch($order)->afterCommit();

            return $order;
        });

        // Solo se elimina el carrito si la transacción terminó correctamente.
        session()->forget('shopping_cart');

        return $order;
    }
}