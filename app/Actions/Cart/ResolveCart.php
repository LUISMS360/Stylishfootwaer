<?php

namespace App\Actions\Cart;

use App\Models\Cart;

class ResolveCart
{
     public function execute(): Cart
    {
        $user = auth()->user();

        if ($user) {
            return Cart::firstOrCreate(['user_id' => $user->id]);
        }

        return Cart::firstOrCreate([
            'user_id'    => null,
            'session_id' => session()->getId(),
        ]);
    }
}
