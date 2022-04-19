<?php

namespace App\services;

use MercadoPago\SDK;
use MercadoPago\Preference;
use MercadoPago\Item;
use MercadoPago\Payer;
use Illuminate\Http\Request;

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
        $this->createPreference($request->value, $request->title);
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

        $preference->save();

        return array(
            'init_point' => $preference->init_point,
            'external_reference' => $preference->external_reference,
            'preference_code' => $preference->id
        );
    }

    public function createItem($title, $value, $quantity = 1): array{
        $item = new Item();

        $item->title = $title;
        $item->quantity = $quantity;
        $item->unit_price = $value;

        return array($item);
    }

    public function createPayer(): array{
        $payer = new Payer();

        $payer->name = 'Lalo';
        $payer->surname = 'Landa';
        $payer->email = 'test_user_46542185@testuser.com';
        $payer->phone = [
            'area_code' => 11,
            'phone' => 959007753,
        ];
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
}