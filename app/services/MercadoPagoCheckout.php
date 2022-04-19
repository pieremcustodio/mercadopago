<?php

namespace App\services;

use MercadoPago\SDK;
use MercadoPago\Preference;
use MercadoPago\Item;
use MercadoPago\Payer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MercadoPagoCheckout{

    protected $key;
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
        $this->createPreference($request->title, $request->value, $request->picture);
    }
    
    
    public function createPreference($title, $value, $picture){
        SDK::initialize();

        $this->resolveat();

        $this->integratorData();

        $preference = new Preference();

        $preference->payer = $this->createPayer();

        $preference->items = $this->createItem($title, $value, $picture);

        $preference->payment_methods = $this->exclude_payment_methods();
        
        $preference->back_urls = $this->url_web();

        $preference->auto_return = 'approved';

        $preference->external_reference = 'piere_07@hotmail.com';

        $preference->notification_url = 'https://piere-mercadopago.herokuapp.com/api/notification?source_news=webhooks';

        $preference->save();

        $response = $this->validate_payment_result($preference);

        return $response;
    }

    public function createItem($title, $value, $picture, $quantity = 1): array{
        $item = new Item();

        $item->title = $title;
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
            'phone' => '959007753',
        );

        return $payer;
    }

    public function exclude_payment_methods(): array
    {
        return [
            'excluded_payment_methods' => [
                'id' => 'visa'
            ],
            'installments' => 6,
        ];
    }

    public function url_web():array{
        return [
            'success' => 'https://pieremcustodio-mp-commerce-php.herokuapp.com/success.php',
            'failure' => 'https://pieremcustodio-mp-commerce-php.herokuapp.com/failure.php',
            'pending' => 'https://pieremcustodio-mp-commerce-php.herokuapp.com/pending.php'
        ];
    }

    public function handleNotification(Request $request)
    {
        $paymentId = $request->data['id'];

        Storage::put("mercadopago/{$paymentId}.json", $request->getContent());

        $payment = $this->searchPayment($paymentId);

        if ($payment && $payment->status == 'approved') {
            return $payment->external_reference;
        };

        return response()->json($request->all(), 201);
    }

    public function searchPayment($paymentId)
    {
        SDK::initialize();
        $this->resolveAccessToken();

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
                'preference_code' => $data->id
            );
        }
    }
}