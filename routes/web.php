<?php

use App\Http\Controllers\Zoho\AccountController;
use App\Http\Controllers\Zoho\AuthenticationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
  return view('welcome');
})->name('home');

Route::get('/create-account-with-deal', [AccountController::class, 'createAccountWithDeal']);
Route::post('/store-account-with-deal', [AccountController::class, 'storeAccountWithDealHandler']);

Route::get('/update-refresh-token', [AuthenticationController::class, 'updateRefreshTokenHandler']);
Route::get('/update-access-token', [AuthenticationController::class, 'updateAccessTokenHandler']);
Route::delete('/clear-tokens', [AuthenticationController::class, 'clearTokens']);
