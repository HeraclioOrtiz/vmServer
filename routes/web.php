<?php

use Illuminate\Support\Facades\Route;
// routes/api.php
Route::get('/', function () {
    return view('welcome');
});
