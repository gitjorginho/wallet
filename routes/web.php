<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WalletController;
Route::get('/', function () {
    return view('welcome');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    //dashboard
    Route::get('/dashboard', [WalletController::class, 'index'])->name('dashboard');
    
    //transferir fundos entre carteiras
    Route::get('/transfer', [WalletController::class, 'transferForm'])->name('transfer.form');
    Route::post('/transfer', [WalletController::class, 'transfer'])->name('transfer');
    
    //realizar depósito em uma carteira
    Route::get('/deposit', [WalletController::class, 'depositForm'])->name('deposit.form');
    Route::post('/deposit', [WalletController::class, 'deposit'])->name('deposit');

    //realizar saque de uma carteira
    Route::get('/withdraw', [WalletController::class, 'withdrawForm'])->name('withdraw.form');
    Route::post('/withdraw', [WalletController::class, 'withdraw'])->name('withdraw');
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
