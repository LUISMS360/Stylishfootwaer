<?php

namespace App\Jobs;

use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendOrderConfirmation implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Order $order
    ) {
    }

    public function handle(): void
    {
        $order = $this->order->fresh([
            'user',
            'items.productVariant.product',
        ]);

        if (! $order || ! $order->user?->email) {
            return;
        }

        Mail::to($order->user->email)
            ->send(new OrderConfirmationMail($order));
    }
}