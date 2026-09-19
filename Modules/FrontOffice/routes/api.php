<?php

use Illuminate\Support\Facades\Route;
use Modules\FrontOffice\Http\Controllers\FrontOfficeController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('frontoffices', FrontOfficeController::class)->names('frontoffice');
});
