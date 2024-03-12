<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ContactController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/





Route::post('/auth/register', [UserController::class, 'register']);
Route::post('/auth/login',[UserController::class,'login'])->name('login');

Route::group(['middleware'=>['auth:sanctum']], function(){
//Route::middleware('auth:api')->group(function () {
    Route::get('auth/profile',[UserController::class,'profile']);
    Route::put('auth/edit-profile',[UserController::class,'edit']);
    Route::post('auth/change-password',[UserController::class,'updatePassword']);
    Route::delete('auth/logout',[UserController::class,'logout']);
    //pour afficher la liste
    Route::get('/tasks', [ContactController::class, 'index']);

    // pour enregistrer une nouelle tache
    Route::post('/tasks', [ContactController::class, 'create']);

    // pour enregistrer la mise à jour de la tache
    Route::put('/tasks/{tasksid}', [ContactController::class, 'update']);
    
    //pour recuperer les details
    Route::get('/tasks/{id}', [ContactController::class, 'getById']);

    // pour supprimer une tache
    Route::delete('/tasks/{id}', [ContactController::class, 'destroy']);

    //Rechercher par le nom
    Route::get("contact/{nom}", [ContactController::class, "getContact"]);
    //Trier par date et par ordre alphabétique
    Route::get("contacts/trier-par/{tri_par}", [ContactController::class, "trierPar"]);
    //Générer le pdf
    Route::get("contacts/generer-pdf", [ContactController::class, "genererPDF"]);
    // Afficher les contacts par catégorie
    Route::get('/contacts/category/{category}', [ContactController::class, 'contactsByCategory']); 

    Route::get('/auth',function(Request $request){
        return $request->user();
    });
});




