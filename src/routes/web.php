<?php

use Illuminate\Support\Facades\Route;
use Ezmu\DocsGenerator\Http\Controllers\DocumentationController;

Route::group([
    'prefix' => config('docs-generator.docs_route', 'docs'),
    'middleware' => config('docs-generator.middlewares', []),
    'as' => 'docs-generator.',
], function () {
        Route::get('/', [DocumentationController::class, 'index'])->name('documentation.index');
    });
