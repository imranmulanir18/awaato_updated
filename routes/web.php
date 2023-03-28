<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('/sign-in');
});

/*--------------------------*LOGIN CONTROLLER*------------------------------ */
Route::get('/sign-in', [LoginController::class, 'index'])->name('sign-in');
Route::post('/sign-in-user', [LoginController::class, 'signIn'])->name('sign-in-user');
Route::post('/sign-out', [LoginController::class, 'signOut'])->name('sign-out');
Route::get('/dashboard', [LoginController::class, 'dashboard'])->name('dashboard');
/*--------------------------*LOGIN CONTROLLER*------------------------------ */

/*--------------------------*REGISTER CONTROLLER*------------------------------ */
Route::get('/sign-up',[RegisterController::class,'index'])->name('sign-up');
Route::post('/sign-up-user',[RegisterController::class,'signUp'])->name('sign-up-user');
Route::get('/get-user-id', [RegisterController::class, 'getUserId'])->name('get-user-id');
Route::post('/get-sponser-id', [RegisterController::class, 'getSponserId'])->name('get-sponser-id');
/*--------------------------*REGISTER CONTROLLER*------------------------------ */
