<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PdfController;

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

//Route::get('/get-category-description/{id}', [HomeController::class, 'getDescription']);
//Route::get('/showObiettivo/{obiettivo}', [HomeController::class, 'showObiettivo'])->name('showObiettivo');






Route::group(['middleware' => ['role:uploader']], routes: function (): void {
    /*Route::get('/showFileUpload', function () {
        return view('file_upload');
    })->name('showFileUpload');*/

    Route::get('/showObiettivo/{obiettivo}', [HomeController::class, 'showObiettivo'])->name('showObiettivo');
    Route::get('/caricamentoPuntoNascite', [HomeController::class, 'caricamentoPuntoNascite'])->name('caricamentoPuntoNascite');
    Route::get('/caricamentoPercorsoCertificabilita', [HomeController::class, 'caricamentoPercorsoCertificabilita'])->name('caricamentoPercorsoCertificabilita');
    Route::get('/uploadTempiListeAttesa', [HomeController::class, 'uploadTempiListaAttesa'])->name('uploadTempiListeAttesa');
    Route::get('/tempiListeAttesa', [HomeController::class, 'tempiListeAttesa'])->name('tempiListeAttesa');
    Route::get('/caricamentoFarmaci', [HomeController::class, 'caricamentoFarmaci'])->name('caricamentoFarmaci');
    Route::post('/farmaciGarePdf', [PdfController::class, 'farmaciGarePdf'])->name('farmaci.gare.autocertificazione');
    Route::post('/farmaciDeliberePdf', [PdfController::class, 'farmaciDeliberePdf'])->name( 'farmaci.gare.deliberazione');

    
    
    Route::post('/uploadObiettivo', [HomeController::class, 'uploadFileObiettivo'])->name('file.uploadObiettivo');
    Route::get('/farmaciIndex', [HomeController::class, 'indexFarmaci'])->name(name: 'indexFarmaci');
    Route::post('/farmaciAutodichiarazione', [PdfController::class, 'farmaciAutodichiarazionePdf'])->name('farmaci.pct.autocertificazione');
    Route::post('/farmaciAutodichiarazioneUpolad', [PdfController::class, 'farmaciAutodichiarazionePdfUpolad'])->name('farmaci.pct.upload');
    
    Route::post('/uploadTempiListeAttesa', [HomeController::class, 'importTarget1'])->name('file.uploadTempiListeAttesa');
    //Route::get(uri: '/indexAmbulatoriale', [HomeController::class, 'indexAmbulatoriale'])->name(name: 'indexAmbulatoriale');

    Route::get('/prontoSoccorso', [HomeController::class, 'prontoSoccorso'])->name('prontoSoccorso');
    Route::get('/caricamentoScreening/{obiettivo}', [HomeController::class, 'caricamentoScreening'])->name('caricamentoScreening');
    Route::get('/caricamentoDonazioni/{obiettivo}', [AdminController::class, 'caricamentoDonazioni'])->name('caricamentoDonazioni');
    Route::get('/donazioni', [HomeController::class, 'donazioni'])->name('donazioni');
    Route::get('/screening', [HomeController::class, 'screening'])->name('screening');
    Route::post('/uploadFileScreening', [HomeController::class, 'uploadFileScreening'])->name('uploadFileScreening');
    Route::post('/uploadDatiDonazione', [HomeController::class, 'uploadDatiDonazione'])->name('uploadDatiDonazione');
    Route::get('/downloadPdf/{obiettivo}', [HomeController::class, 'downloadPdf'])->name('downloadPdf');
    Route::post('/mmgRegister', [HomeController::class, 'mmgRegister'])->name('mmgRegister');
    Route::get('/garanziaLea', [HomeController::class, 'garanziaLea'])->name('garanziaLea');
    Route::post('/aggiornaGraficiGaranzia', [HomeController::class, 'garanziaLea'])->name('aggiornaGraficiGaranzia');
    Route::get('/fse', [HomeController::class, 'fse'])->name('fse');
});




Route::group(attributes: ['middleware' => ['role:controller']], routes: function (): void {
    Route::get('/controller/home', [AdminController::class, 'indexController'])->name('controller.home');
    Route::get('/controller/showObiettivo/{obiettivo}', [AdminController::class, 'showObiettivo'])->name('controller.obiettivo');
    Route::post('/controller/valide', [AdminController::class, 'valide'])->name('controller.valide');
    Route::post('/controller/notValide', [AdminController::class, 'notValide'])->name('controller.notValide');
    Route::post('/approvaObiettivo', action: [AdminController::class, 'approvaObiettivo'])->name('approvaObiettivo');

});


