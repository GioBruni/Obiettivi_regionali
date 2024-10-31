<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes(['register' => false, 'verify' => true, 'reset' => true]);

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::group(['middleware' => ['role:user manager']], routes: function (): void {
    //Route::get('/register', [App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::get('/register', function() {
        return view('auth.register');
    })->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    Route::get('/usersList', [AdminController::class, 'usersList'])->name('usersList');
    Route::get('/showUser/{id?}', [AdminController::class, 'showUser'])->name('showUser');
    Route::post('/registerUser', [AdminController::class, 'registerUser'])->name( 'registerUser');
    Route::post('/structureDelete', [AdminController::class, 'structureDelete'])->name( 'structure.delete');
    Route::post('/structureInsert', [AdminController::class, 'structureInsert'])->name( 'structure.insert');
    Route::post('/enableUser', [AdminController::class, 'enableUser'])->name('enableUser');
    
});

Route::group(['middleware' => ['role:uploader']], routes: function (): void {
    /*Route::get('/showFileUpload', function () {
        return view('file_upload');
    })->name('showFileUpload');*/

    Route::get('/showObiettivo/{obiettivo}', [HomeController::class, 'showObiettivo'])->name('showObiettivo');
    Route::post('/uploadObiettivo', [HomeController::class, 'uploadFileObiettivo'])->name('file.uploadObiettivo');
    Route::get('/prontoSoccorso', [HomeController::class, 'prontoSoccorso'])->name('prontoSoccorso');
    Route::get('/donazioni', [HomeController::class, 'donazioni'])->name('donazioni');
    Route::get('/tempiListeAttesa', [HomeController::class, 'tempiListeAttesa'])->name('tempiListeAttesa');
    Route::get('/screening', [HomeController::class, 'screening'])->name('screening');
    
});

Route::group(attributes: ['middleware' => ['role:controller']], routes: function (): void {
    Route::get('/controller/home', [AdminController::class, 'indexController'])->name('controller.home');
    Route::get('/controller/showObiettivo/{obiettivo}', [AdminController::class, 'showObiettivo'])->name('controller.obiettivo');
    Route::post('/controller/valide', [AdminController::class, 'valide'])->name('controller.valide');
    Route::post('/controller/notValide', [AdminController::class, 'notValide'])->name('controller.notValide');
    
});


