<?php

use BoltAudit\App\Http\Controllers\PluginController;
use BoltAudit\App\Http\Controllers\PostController;
use BoltAudit\App\Http\Controllers\ReportController;
use BoltAudit\App\Http\Controllers\DatabaseController;
use BoltAudit\WpMVC\Routing\Route;

Route::post( 'reports/{type}', [ReportController::class, 'index'], ['admin'] );
Route::post( 'plugin/{slug}', [PluginController::class, 'index'], ['admin'] );
Route::post( 'posts/details', [PostController::class, 'index'], ['admin'] );
Route::post( 'database/details', [DatabaseController::class, 'index'], ['admin'] );
