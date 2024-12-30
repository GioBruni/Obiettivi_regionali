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

Route::group(['middleware' => ['role:user_manager']], routes: function (): void {
    Route::get('/admin/home', [AdminController::class, 'index'])->name('admin.home');

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

    Route::get('/admin/tempiListeAttesa', [AdminController::class, 'tempiListeAttesa'])->name('admin.tempiListeAttesa');
    Route::get('/admin/esiti', [AdminController::class, 'esiti'])->name('admin.esiti');
    Route::get('/admin/puntiNascita', [AdminController::class, 'puntiNascita'])->name('puntiNascita');
    Route::get('/admin/prontoSoccorso', [AdminController::class, 'prontoSoccorso'])->name('admin.prontoSoccorso');
    Route::get('/admin/screening', [AdminController::class, 'screening'])->name('admin.screening');
    Route::get('/admin/donazioni', [AdminController::class, 'donazioni'])->name('admin.donazioni');
    Route::get('/admin/fse', [AdminController::class, 'fse'])->name('admin.fse');
    Route::get('/admin/certificabilita', [AdminController::class, 'certificabilita'])->name('admin.certificabilita');
    Route::get('/admin/farmaci', [AdminController::class, 'farmaci'])->name( 'admin.farmaci');

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
    Route::get('/tempiListeAttesa', [HomeController::class, 'tempiListeAttesa'])->name('tempiListeAttesa');
    Route::get('/caricamentoFarmaci', [HomeController::class, 'caricamentoFarmaci'])->name('caricamentoFarmaci');
    Route::post('/farmaciGarePdf', [PdfController::class, 'farmaciGarePdf'])->name('farmaci.gare.autocertificazione');
    Route::post('/farmaciDeliberePdf', [PdfController::class, 'farmaciDeliberePdf'])->name( 'farmaci.gare.deliberazione');
    Route::get('/uploadTempiListeAttesa/{year?}', [HomeController::class, 'uploadTempiListaAttesa'])->name('uploadTempiListeAttesa');

    
    
    Route::post('/uploadObiettivo', [HomeController::class, 'uploadFileObiettivo'])->name('file.uploadObiettivo');
    Route::get('/farmaciIndex', [HomeController::class, 'indexFarmaci'])->name(name: 'indexFarmaci');
    Route::post('/farmaciAutodichiarazione', [PdfController::class, 'farmaciAutodichiarazionePdf'])->name('farmaci.pct.autocertificazione');
    Route::post('/farmaciAutodichiarazioneUpolad', [PdfController::class, 'farmaciAutodichiarazionePdfUpolad'])->name('farmaci.pct.upload');
    
    Route::post('/importTarget1', [HomeController::class, 'importTarget1'])->name('importTarget1');
    //Route::get(uri: '/indexAmbulatoriale', [HomeController::class, 'indexAmbulatoriale'])->name(name: 'indexAmbulatoriale');
    Route::post('/saveTempiListeAttesa', [HomeController::class, 'saveTempiListeAttesa'])->name('saveTempiListeAttesa');

    Route::get('/prontoSoccorso', [HomeController::class, 'prontoSoccorso'])->name('prontoSoccorso');
    Route::get('/caricamentoScreening', [HomeController::class, 'caricamentoScreening'])->name('caricamentoScreening');
    Route::post('/importTarget5LEA', [HomeController::class, 'importTarget5LEA'])->name('importTarget5LEA');
    Route::get('/caricamentoDonazioni', [HomeController::class, 'caricamentoDonazioni'])->name('caricamentoDonazioni');
    Route::get('/caricamentoFse/{obiettivo}', [HomeController::class, 'caricamentoFse'])->name('caricamentoFse');
    Route::get('/caricamentoGaranziaLea/{obiettivo}', [HomeController::class, 'caricamentoGaranziaLea'])->name('caricamentoGaranziaLea');
    Route::get('/donazioni', [HomeController::class, 'donazioni'])->name('donazioni');
    Route::get('/screening', [HomeController::class, 'screening'])->name('screening');
    Route::get('/esiti', [HomeController::class, 'esiti'])->name('esiti');
    Route::post('/uploadFileScreening', [HomeController::class, 'uploadFileScreening'])->name('uploadFileScreening');
    Route::post('/uploadDatiDonazione', [HomeController::class, 'uploadDatiDonazione'])->name('uploadDatiDonazione');
    Route::post('/uploadDatiFse', [HomeController::class, 'uploadDatiFse'])->name('uploadDatiFse');
    Route::post('/uploadFileScreeningAo', [HomeController::class, 'uploadFileScreeningAo'])->name('uploadFileScreeningAo');

    Route::post('/uploadDatiVeterinaria', [HomeController::class, 'uploadDatiVeterinaria'])->name('uploadDatiVeterinaria');
    Route::post('/uploadDatilea', [HomeController::class, 'uploadDatiLea'])->name('uploadDatiLea');

    Route::post('/uploadDatiCombinati', [HomeController::class, 'uploadDatiCombinati'])->name('uploadDatiCombinati');

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


