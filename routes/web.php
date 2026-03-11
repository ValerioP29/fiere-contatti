<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\ExhibitionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicContactController;
use App\Models\Contact;
use App\Models\Exhibition;
use Illuminate\Support\Facades\Route;
use App\Support\TenantContext;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('exhibitions.index')
        : redirect()->route('login');
});

/**
 * Dashboard Breeze (opzionale)
 * Se non la usi, puoi pure toglierla.
 */
Route::get('/dashboard', function () {
    $tenantId = app(TenantContext::class)->currentFromRequest(request())->id;

    $latestExhibitions = Exhibition::query()
        ->where('tenant_id', $tenantId)
        ->latest()
        ->take(5)
        ->get();

    return view('dashboard', [
        'exhibitionsCount' => Exhibition::query()->where('tenant_id', $tenantId)->count(),
        'contactsCount' => Contact::query()->where('tenant_id', $tenantId)->count(),
        'latestExhibitions' => $latestExhibitions,
    ]);
})->middleware(['auth', 'verified', 'ensure.current.tenant'])->name('dashboard');

Route::get('/tenant/missing', function () {
    return response()->view('tenant.missing', status: 403);
})->middleware('auth')->name('tenant.missing');

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
Route::middleware(['auth', 'ensure.current.tenant'])->group(function () {
    Route::resource('exhibitions', ExhibitionController::class);

    Route::post('/exhibitions/{exhibition}/public-link', [ExhibitionController::class, 'generatePublicLink'])
        ->name('exhibitions.public-link');

    Route::post('/exhibitions/{exhibition}/contacts', [ContactController::class, 'store'])->name('exhibitions.contacts.store');
    Route::put('/exhibitions/{exhibition}/contacts/{contact}', [ContactController::class, 'update'])->name('exhibitions.contacts.update');
    Route::delete('/exhibitions/{exhibition}/contacts/{contact}', [ContactController::class, 'destroy'])->name('exhibitions.contacts.destroy');

    Route::get('/exhibitions/{exhibition}/contacts-export', [ContactController::class, 'exportExcel'])->name('exhibitions.contacts.export');
    Route::get('/exhibitions/{exhibition}/contacts/{contact}/file/download', [ContactController::class, 'downloadFile'])
        ->name('exhibitions.contacts.download');
    Route::get('/exhibitions/{exhibition}/contacts/{contact}/file/preview', [ContactController::class, 'previewFile'])
        ->name('exhibitions.contacts.preview');
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
