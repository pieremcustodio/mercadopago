<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/pay', 'PagoController@makePayment')->name('makePayment');
Route::post('/notification', 'NotificacionController@notification')->name('notification');
