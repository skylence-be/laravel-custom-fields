<?php

use Illuminate\Support\Facades\Route;
use Xve\LaravelCustomFields\Http\Livewire\FieldActivityLog;
use Xve\LaravelCustomFields\Http\Livewire\FieldCreate;
use Xve\LaravelCustomFields\Http\Livewire\FieldEdit;
use Xve\LaravelCustomFields\Http\Livewire\FieldIndex;

Route::prefix(config('custom-fields.route.prefix'))
    ->middleware(config('custom-fields.route.middleware'))
    ->name(config('custom-fields.route.name_prefix'))
    ->group(function () {
        Route::get('/', FieldIndex::class)->name('index');
        Route::get('/create', FieldCreate::class)->name('create');
        Route::get('/{field}/edit', FieldEdit::class)->name('edit');
        Route::get('/{field}/activity', FieldActivityLog::class)->name('activity');
    });
