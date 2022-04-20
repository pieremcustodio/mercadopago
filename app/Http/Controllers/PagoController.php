<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\services\MercadoPagoCheckout;
use Illuminate\Support\Facades\Validator;

class PagoController extends Controller
{
    public function makePayment(Request $request){
        $validator = Validator::make($request->all(), [
            'value' => ['required', 'numeric'],
            'title' => ['required'],
        ]);

        $paymentPlatform = resolve(MercadoPagoCheckout::class);

        $response = $paymentPlatform->handlePayment($request);

        return response()->json($response);
    }
}
