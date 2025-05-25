<?php

use App\Http\Controllers\Zoho\AccountController;
use App\Http\Controllers\Zoho\DealController;
use App\Http\Controllers\Zoho\AuthenticationController;
use App\Models\ZohoAuthToken;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
  return view('welcome');
})->name('home');

Route::get('/create-account-with-deal', function () {
  $hasTokens = ZohoAuthToken::first();

  return Inertia::render('CreateAccountWithDeal', ['hasTokens' => !!$hasTokens]);
})->name('create.account-with-deal');

Route::resource('/accounts', AccountController::class);

Route::resource('/accounts.deals', DealController::class)->shallow();

Route::post('/refresh-token', [AuthenticationController::class, 'updateRefreshToken'])->name('refresh.token');
Route::put('/access-token', [AuthenticationController::class, 'updateAccessToken'])->name('access.token');
Route::delete('/clear-tokens', [AuthenticationController::class, 'clearTokens'])->name('clear.tokens');
