<?php

namespace App\services;

use MercadoPago\SDK;
use MercadoPago\Preference;
use MercadoPago\Item;
use MercadoPago\Payer;
use MercadoPago\Payment;
use Illuminate\Http\Request;

class MercadoPagoCheckout{

    protected $secret;
    protected $integrador_id;

    public function __construct(){
        $this->secret = config('services.mercadopago.access_token');
        $this->integrador_id = config('services.mercadopago.integrator_id');
    }
    
    public function integratorData(): void
    {
        SDK::setIntegratorId($this->integrador_id);
    }

    public function resolveat(){
        SDK::setAccessToken($this->secret);
    }

    public function handlePayment(Request $request){
        return $this->createPreference($request->title, $request->value);
    }
    
    
    public function createPreference($title, $value){
        SDK::initialize();

        $this->resolveat();

        $this->integratorData();

        $preference = new Preference();

        $preference->payer = $this->createPayer();

        $preference->items = $this->createItem($title, $value);

        $preference->payment_methods = $this->exclude_payment_methods();
        
        $preference->back_urls = $this->url_web();

        $preference->auto_return = 'approved';

        $preference->external_reference = 'piere_07@hotmail.com';

        $preference->notification_url = 'https://webhook.site/2ec3931c-7f36-4e40-9209-0c9d51714033';

        $preference->save();

        $response = $this->validate_payment_result($preference);

        return $response;
    }

    public function createItem($title, $value, $picture = 'https://www.mercadopago.com/org-img/MP3/home/logomp3.gif', $itemId = '4715',$quantity = 1): array{
        $item = new Item();

        $item->id = $itemId;
        $item->title = $title;
        $item->description = 'Dispositivo móvil de Tienda e-commerce';
        $item->quantity = $quantity;
        $item->unit_price = $value;
        $item->picture_url = $picture;

        return array($item);
    }

    public function createPayer(){
        $payer = new Payer();

        $payer->name = 'Lalo';
        $payer->surname = 'Landa';
        $payer->email = 'test_user_46542185@testuser.com';
        $payer->phone = array(
            'area_code' => '11',
            'number' => '932932932',
        );

        $payer->address = array(
            'street_name' => 'street',
            'street_number' => '123',
            'zip_code' => '1234',
        );

        return $payer;
    }

    public function exclude_payment_methods(): array
    {
        return [
            'excluded_payment_methods' => array(
                array('id' => 'visa')
            ),
            'excluded_payment_types' => array(
                array('id' => 'visa')
            ),
            'installments' => 6,
        ];
    }

    public function url_web():array{
        return [
            'success' => 'https://mp-front-kh5h0f3wl-pieremcustodio.vercel.app/success',
            'failure' => 'https://mp-front-kh5h0f3wl-pieremcustodio.vercel.app/failure',
            'pending' => 'https://mp-front-kh5h0f3wl-pieremcustodio.vercel.app/pending'
        ];
    }

    public function handleNotification(Request $request)
    {
        $paymentId = $request->data['id'];

        $payment = $this->searchPayment($paymentId);

        if ($payment && $payment->status == 'approved') {
            return $payment->external_reference;
        };

        return response()->json($request->all(), 201);
    }

    public function searchPayment($paymentId)
    {
        SDK::initialize();
        $this->resolveat();

        return Payment::find_by_id($paymentId);
    }
    
    public function validate_payment_result($data){
        if($data->id === null) {
            $error_message = 'Error desconocido';
    
            if($data->error !== null) {
                $sdk_error_message = $data->error->message;
                $error_message = $sdk_error_message !== null ? $sdk_error_message : $error_message;
            }

            return $response = array(
                'error' => $error_message
            );
        }else{
            return $response = array(
                'init_point' => $data->init_point,
                'external_reference' => $data->external_reference,
                'preference_code' => $data->id,
            );
        }
    }
}