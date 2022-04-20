<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\services\MercadoPagoCheckout;

class NotificacionController extends Controller
{
    public function notification(Request $request)
    {
        if ($request->type == 'payment') {
            $paymentPlatform = resolve(MercadoPagoCheckout::class);
            $payment = $paymentPlatform->handleNotification($request);

            if ($payment) {

                return response()->json([
                    'message' => 'Pago realizado',
                    'code' => 201,
                ], 201);
            }
        }

        return response()->json($request->all(), 201);
    }
}
