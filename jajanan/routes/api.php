<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/getProduk', [App\Http\Controllers\HomeController::class, 'getProduk']);
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'loginApi']);
Route::post('/storeApi', [App\Http\Controllers\Admin\ProdukAdminController::class, 'storeApi']);
Route::get('/kategoriApi', [App\Http\Controllers\Admin\ProdukAdminController::class, 'kategoriApi']);
Route::get('/editApi/{id}', [App\Http\Controllers\Admin\ProdukAdminController::class, 'editApi']);
Route::put('/updateApi/{id}', [App\Http\Controllers\Admin\ProdukAdminController::class, 'updateApi']);
Route::delete('/deleteApi/{id}', [App\Http\Controllers\Admin\ProdukAdminController::class, 'destroyApi']);


Route::get('/getUser', [App\Http\Controllers\Admin\ProdukAdminController::class, 'userApi']);
//register

Route::post('/registerApi', [App\Http\Controllers\Auth\RegisterController::class,'registerApi']);
Route::put('/customer/fotoApi/{id}', [App\Http\Controllers\Customer\ProfileCustomerController::class, 'update_fotoApi']);

//kategori
//Route::get('/kategoriGetApi', [App\Http\Controllers\Admin\KategoriAdminController::class, 'getKategori']);
Route::post('/kategoriStoreApi', [App\Http\Controllers\Admin\KategoriAdminController::class, 'storeApi']);
Route::get('/kategoriEditApi/{id}', [App\Http\Controllers\Admin\KategoriAdminController::class, 'editApi']);
Route::put('/kategoriUpdateApi/{id}', [App\Http\Controllers\Admin\KategoriAdminController::class, 'updateApi']);
Route::delete('/kategoriDeleteApi/{id}', [App\Http\Controllers\Admin\KategoriAdminController::class, 'destroyApi']);

// Keranjang (Cart)
Route::post('/keranjang/storeApi', [App\Http\Controllers\Customer\KeranjangCustomerController::class, 'storeApi']); 
Route::get('/keranjang/indexApi', [App\Http\Controllers\Customer\KeranjangCustomerController::class, 'indexApi']); 
Route::put('/keranjang/updateApi/{id}', [App\Http\Controllers\Customer\KeranjangCustomerController::class, 'updateApi']); 
Route::delete('/keranjang/deleteApi/{id}', [App\Http\Controllers\Customer\KeranjangCustomerController::class, 'deleteApi']); 

Route::post('/pesanan/storeApi', [App\Http\Controllers\Customer\PesananCustomerController::class,'storeApi']);
Route::post('/pesanan/cancelApi', [App\Http\Controllers\Customer\PesananCustomerController::class,'cancelApi']);
Route::get('/pesanan/indexApi', [App\Http\Controllers\Customer\PesananCustomerController::class, 'indexApi']); 

Route::get('/pesananAdminApi', [App\Http\Controllers\Admin\PesananAdminController::class,'indexApi']);
Route::put('/pesananAdminKonfirmasiApi/{id}', [App\Http\Controllers\Admin\PesananAdminController::class,'konfirmasiApi']);
Route::put('/pesananAdminStatusApi/{id}/{status}', [App\Http\Controllers\Admin\PesananAdminController::class, 'updateStatusApi']);
Route::get('/pengirimanAdminApi', [App\Http\Controllers\Admin\PesananAdminController::class, 'pengirimanApi']);
Route::post('/pesananAdminKirimApi', [App\Http\Controllers\Admin\PesananAdminController::class, 'pesananKirimApi']);
Route::get('/resiApi/{id}', [App\Http\Controllers\Admin\PesananAdminController::class, 'resiByPesananApi']);

Route::put('/profilePasswordApi/{id}', [App\Http\Controllers\Customer\ProfileCustomerController::class,'updatePasswordApi']);
Route::get('/profileApi/{id}', [App\Http\Controllers\Customer\ProfileCustomerController::class,'showApi']);
Route::put('/profileApi/{id}', [App\Http\Controllers\Customer\ProfileCustomerController::class,'updateApi']);
Route::post('/profileFotoApi/{id}', [App\Http\Controllers\Customer\ProfileCustomerController::class,'update_fotoApi']);

Route::get('/alamatApi/{userId}', [App\Http\Controllers\Customer\AlamatUserController::class,'indexApi']);
Route::post('/alamatStoreApi', [App\Http\Controllers\Customer\AlamatUserController::class,'storeApi']);
Route::put('/alamatUpdateApi/{id}', [App\Http\Controllers\Customer\AlamatUserController::class,'updateApi']);
Route::delete('/alamatDeleteApi/{id}', [App\Http\Controllers\Customer\AlamatUserController::class,'deleteApi']);

