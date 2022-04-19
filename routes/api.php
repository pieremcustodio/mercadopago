<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/payments/pay', 'PagoController@makePayment')->name('makePayment');