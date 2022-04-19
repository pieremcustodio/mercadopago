<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\services\MercadoPagoCheckout;


class PagoController extends Controller
{
    public function makePayment(Request $request){

        $paymentPlatform = resolve(MercadoPagoCheckout::class);

        $response = $paymentPlatform->handlePayment($request);

        return response()->json($response);
    }
}
