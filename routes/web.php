<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\ExhibitionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicContactController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Se vuoi la welcome di Breeze, metti: return view('welcome');
    // Ma per la tua app ha più senso andare subito alle fiere.
    return redirect()->route('exhibitions.index');
});

/**
 * Dashboard Breeze (opzionale)
 * Se non la usi, puoi pure toglierla.
 */
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

/**
 * Profile Breeze (opzionale)
 */
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/**
 * Le TUE rotte protette (CRUD fiere + contatti)
 */
Route::middleware(['auth'])->group(function () {
    Route::get('/exhibitions', [ExhibitionController::class, 'index'])->name('exhibitions.index');
    Route::get('/exhibitions/create', [ExhibitionController::class, 'create'])->name('exhibitions.create');
    Route::post('/exhibitions', [ExhibitionController::class, 'store'])->name('exhibitions.store');
    Route::get('/exhibitions/{exhibition}', [ExhibitionController::class, 'show'])->name('exhibitions.show');
    Route::get('/exhibitions/{exhibition}/edit', [ExhibitionController::class, 'edit'])->name('exhibitions.edit');
    Route::put('/exhibitions/{exhibition}', [ExhibitionController::class, 'update'])->name('exhibitions.update');
    Route::delete('/exhibitions/{exhibition}', [ExhibitionController::class, 'destroy'])->name('exhibitions.destroy');

    Route::post('/exhibitions/{exhibition}/public-link', [ExhibitionController::class, 'generatePublicLink'])
        ->name('exhibitions.public-link');

    Route::get('/exhibitions/{exhibition}/contacts', [ContactController::class, 'index'])->name('contacts.index');
    Route::post('/exhibitions/{exhibition}/contacts', [ContactController::class, 'store'])->name('contacts.store');
    Route::put('/exhibitions/{exhibition}/contacts/{contact}', [ContactController::class, 'update'])->name('contacts.update');
    Route::delete('/exhibitions/{exhibition}/contacts/{contact}', [ContactController::class, 'destroy'])->name('contacts.destroy');

    Route::get('/exhibitions/{exhibition}/contacts-export', [ContactController::class, 'exportExcel'])->name('contacts.export');
    Route::get('/exhibitions/{exhibition}/contacts/{contact}/file/download', [ContactController::class, 'downloadFile'])
        ->name('contacts.file.download');
    Route::get('/exhibitions/{exhibition}/contacts/{contact}/file/preview', [ContactController::class, 'previewFile'])
        ->name('contacts.file.preview');
});

/**
 * Rotte pubbliche per form via token
 */
Route::get('/p/{token}', [PublicContactController::class, 'show'])->name('public.form');
Route::post('/p/{token}', [PublicContactController::class, 'store'])
    ->middleware('throttle:20,1')
    ->name('public.store');
Route::get('/p/{token}/thanks', [PublicContactController::class, 'thanks'])->name('public.thanks');

require __DIR__.'/auth.php';