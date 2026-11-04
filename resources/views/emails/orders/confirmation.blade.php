<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Confirmación de pedido</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.6;">
    <h1>¡Gracias por tu compra!</h1>

    <p>Hola {{ $order->user->name }},</p>

    <p>Hemos registrado tu pedido correctamente.</p>

    <h2>Pedido #{{ $order->id }}</h2>

    <p>
        <strong>Estado:</strong>
        {{ $order->status->value ?? $order->status }}
    </p>

    <p>
        <strong>Dirección de envío:</strong>
        {{ $order->shipping_address }}
    </p>

    <table width="100%" cellpadding="8" cellspacing="0" border="1"
           style="border-collapse: collapse; border-color: #e5e7eb;">
        <thead>
            <tr>
                <th align="left">Producto</th>
                <th align="left">Variante</th>
                <th align="right">Cantidad</th>
                <th align="right">Precio unitario</th>
                <th align="right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                @php
                    $variant = $item->productVariant;
                    $product = $variant?->product;
                @endphp

                <tr>
                    <td>{{ $product?->name ?? 'Producto' }}</td>
                    <td>
                        {{ $variant?->size ?? 'N/A' }}
                        /
                        {{ $variant?->color ?? 'N/A' }}
                    </td>
                    <td align="right">{{ $item->quantity }}</td>
                    <td align="right">
                        S/ {{ number_format((float) $item->unit_price, 2) }}
                    </td>
                    <td align="right">
                        S/ {{ number_format(
                            (float) $item->unit_price * $item->quantity,
                            2
                        ) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3>
        Total del pedido:
        S/ {{ number_format((float) $order->total, 2) }}
    </h3>

    <p>Gracias por confiar en nuestra tienda.</p>
</body>
</html>