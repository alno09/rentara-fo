<?php

use Illuminate\Support\Facades\Route;
use Modules\FrontOffice\Http\Controllers\FrontOfficeController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('frontoffices', FrontOfficeController::class)->names('frontoffice');
});
