<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\CredentialController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\InstallController;
use Illuminate\Support\Facades\Route;

/* |-------------------------------------------------------------------------- | Web Routes |-------------------------------------------------------------------------- | | Here is where you can register web routes for your application. These | routes are loaded by the RouteServiceProvider and all of them will | be assigned to the "web" middleware group. Make something great! | */

// Installation Routes
Route::prefix('install')->name('install.')->group(function () {
    Route::get('/welcome', [InstallController::class , 'welcome'])->name('welcome');
    Route::get('/database', [InstallController::class , 'database'])->name('database');
    Route::post('/database', [InstallController::class , 'storeDatabase'])->name('storeDatabase');
    Route::get('/status', [InstallController::class , 'status'])->name('status');
    Route::get('/migrations', [InstallController::class , 'migrations'])->name('migrations');
    Route::post('/migrations/run', [InstallController::class , 'runMigrations'])->name('runMigrations');
    Route::get('/migrations/status', [InstallController::class , 'migrationStatus'])->name('migrationStatus');
    Route::get('/admin', [InstallController::class , 'admin'])->name('admin');
    Route::post('/admin', [InstallController::class , 'storeAdmin'])->name('storeAdmin');
    Route::get('/organization', [InstallController::class , 'organization'])->name('organization');
    Route::post('/organization', [InstallController::class , 'storeOrganization'])->name('storeOrganization');
    Route::get('/network', [InstallController::class , 'network'])->name('network');
    Route::post('/finish', [InstallController::class , 'finish'])->name('finish');
});


Route::get('/', [DashboardController::class , 'root'])->middleware(['auth', 'verified', 'two-factor'])->name('root');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('organizations', OrganizationController::class)->middleware('organization');
    Route::post('/organizations/{organization:slug}/notes', [OrganizationController::class , 'updateNotes'])->name('organizations.notes.update');
    Route::patch('/organizations/{organization}/suspend', [OrganizationController::class , 'suspend'])->name('organizations.suspend');
    Route::patch('/organizations/{organization}/activate', [OrganizationController::class , 'activate'])->name('organizations.activate');
    // Role Management
    Route::get('/roles', [App\Http\Controllers\RoleController::class , 'index'])->name('roles.index');
    Route::put('/roles/{role}', [App\Http\Controllers\RoleController::class , 'update'])->name('roles.update');
    Route::get('/settings', [SettingsController::class , 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class , 'update'])->name('settings.update');

    // Global Search API
    Route::get('/api/search', [SearchController::class , 'index'])->name('api.search');

    // Favorites
    Route::post('/api/favorites/toggle', [App\Http\Controllers\FavoriteController::class , 'toggle'])->name('api.favorites.toggle');

    // User Management
    Route::resource('users', \App\Http\Controllers\UserController::class);
    Route::post('users/{user}/organizations', [\App\Http\Controllers\UserController::class , 'attachOrganization'])->name('users.organizations.attach');
    Route::delete('users/{user}/organizations/{organization}', [\App\Http\Controllers\UserController::class , 'detachOrganization'])->name('users.organizations.detach');
});

Route::middleware(['auth', 'verified', 'organization'])->group(function () {
    Route::prefix('{organization}')->group(function () {
            Route::get('/dashboard', [DashboardController::class , 'index'])->name('dashboard');

            Route::get('/assets/import', [AssetController::class , 'importForm'])->name('assets.import');
            Route::post('/assets/import', [AssetController::class , 'importProcess'])->name('assets.import.process');
            Route::get('/assets/export', [AssetController::class , 'export'])->name('assets.export');
            Route::resource('assets', AssetController::class);

            Route::resource('credentials', CredentialController::class);
            Route::get('credentials/{credential}/reveal', [CredentialController::class , 'reveal'])->name('credentials.reveal');

            Route::resource('documents', DocumentController::class);
            Route::get('documents/{document}/download', [DocumentController::class , 'download'])->name('documents.download');
            Route::get('documents/{document}/preview', [DocumentController::class , 'preview'])->name('documents.preview');

            Route::resource('sites', SiteController::class);
            Route::patch('/sites/{site}/suspend', [SiteController::class , 'suspend'])->name('sites.suspend');
            Route::patch('/sites/{site}/activate', [SiteController::class , 'activate'])->name('sites.activate');
            Route::post('/sites/{site}/notes', [SiteController::class , 'updateNotes'])->name('sites.notes.update');

            Route::resource('contacts', App\Http\Controllers\ContactController::class);

            // Relationship Routes
            Route::post('/relationships', [App\Http\Controllers\RelationshipController::class , 'store'])->name('relationships.store');
            Route::delete('/relationships/{relationship}', [App\Http\Controllers\RelationshipController::class , 'destroy'])->name('relationships.destroy');
        }
        );
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class , 'edit'])->name('profile.edit');
    Route::put('/roles/{role}', [App\Http\Controllers\RoleController::class , 'update'])->name('roles.update');
    Route::get('/audit-logs/export', [\App\Http\Controllers\AuditLogController::class , 'export'])->name('audit-logs.export');
    Route::get('/audit-logs', [\App\Http\Controllers\AuditLogController::class , 'index'])->name('audit-logs.index');
    Route::patch('/profile', [ProfileController::class , 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class , 'destroy'])->name('profile.destroy');

    // Two-Factor Authentication
    Route::get('2fa/setup', [TwoFactorController::class , 'setup'])->name('2fa.setup');
    Route::post('2fa/enable', [TwoFactorController::class , 'enable'])->name('2fa.enable');
    Route::post('2fa/disable', [TwoFactorController::class , 'disable'])->name('2fa.disable');

    // 2FA Challenge (Accessible even if 2FA not verified yet)
    Route::get('2fa/challenge', [TwoFactorController::class , 'challenge'])->name('2fa.challenge');
    Route::post('2fa/verify-challenge', [TwoFactorController::class , 'verifyChallenge'])->name('2fa.verify_challenge');
});

// Protect the rest of the app with 'two-factor' middleware
Route::middleware(['auth', 'verified', 'two-factor'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class , 'global'])->name('dashboard.global');
    Route::get('/global-dashboard', [\App\Http\Controllers\DashboardController::class , 'global'])->name('global.dashboard');
});

// OAuth / SSO Routes (must be public — no auth middleware)
Route::get('/auth/{provider}/redirect', [\App\Http\Controllers\Auth\SocialAuthController::class , 'redirect'])
    ->name('social.redirect')
    ->where('provider', 'google|microsoft');
Route::get('/auth/{provider}/callback', [\App\Http\Controllers\Auth\SocialAuthController::class , 'callback'])
    ->name('social.callback')
    ->where('provider', 'google|microsoft');

require __DIR__ . '/auth.php';
