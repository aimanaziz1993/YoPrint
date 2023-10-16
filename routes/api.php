<?php

use Illuminate\Support\Facades\Route;


Route::group(['namespace' => 'App\Http\Controllers\API'], function() { 

    Route::post('/upload', 'UploadApiController@upload')->name('api.upload');
    Route::post('/progress', 'UploadApiController@batch')->name('api.progress');
    Route::get('/batches', 'UploadApiController@batches')->name('api.batches');
});

