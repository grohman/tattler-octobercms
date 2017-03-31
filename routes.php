<?php

Route::get('_tattler/ws', 'Grohman\Tattler\Controllers\TattlerController@getWs');
Route::get('_tattler/channels', 'Grohman\Tattler\Controllers\TattlerController@getChannels');
Route::get('_tattler/auth', 'Grohman\Tattler\Controllers\TattlerController@getAuth');