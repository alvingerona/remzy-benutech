<?php 
Route::get('/property/{id}/benutech', ['uses' => 'BenutechController@index', 'as' => 'property.single.benutech'] );

/**
 * Debug
 */

Route::middleware([])->prefix('debug')->namespace('\CaffeineInteractive\Remzy\App\Controllers\Debug')->group(function () {
	Route::get('benutech', ['uses' => 'BenutechController@index']);
});