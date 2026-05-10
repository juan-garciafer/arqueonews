<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CarpetaController;
use App\Http\Controllers\NoticiasController;

Route::redirect('/', '/noticias');

Route::get('/noticias', [NoticiasController::class, 'index'])->name('noticias.index');
Route::get('/pais/{pais}', [NoticiasController::class, 'porPais'])->name('noticias.pais');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');    
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('carpetas', CarpetaController::class);

    // Routes for managing news in folders
    Route::post('/carpetas/{id}/agregar-noticia', [CarpetaController::class, 'addNoticia'])->name('carpetas.agregar-noticia');
    Route::delete('/carpetas/{id}/noticia/{noticia_id}', [CarpetaController::class, 'removeNoticia'])->name('carpetas.remover-noticia');
    Route::get('/mis-carpetas', [CarpetaController::class, 'getCarpetasJson'])->name('carpetas.json');
});

require __DIR__ . '/auth.php';
