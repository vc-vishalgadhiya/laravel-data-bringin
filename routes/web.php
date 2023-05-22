<?php

use Illuminate\Support\Facades\Route;
use Vcian\LaravelDataBringin\Http\Controllers\ImportController;

Route::group([
    'middleware' => config('data-bringin.middleware'),
    'prefix' => config('data-bringin.path'),
    'as' => 'data_bringin.',
], function () {
    Route::get('/', [ImportController::class, 'index'])->name('index');
    Route::post('/', [ImportController::class, 'store'])->name('store');
    Route::get('/delete/{id}', [ImportController::class, 'delete'])->name('delete');
    Route::get('/logs', [ImportController::class, 'logs'])->name('logs');
    Route::get('/failed-records/download/{id}', [ImportController::class, 'downloadFailedRecords'])->name('failed_records.download');
});
