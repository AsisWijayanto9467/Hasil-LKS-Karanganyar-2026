<?php

use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\GameController;
use App\Http\Controllers\API\ScoreController;
use App\Http\Controllers\API\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;




// Route::prefix("v1")->group(function() {
//     Route::prefix('auth')->group(function() {
//         Route::post("/signup", [AuthController::class, "signup"]);
//         Route::post("/signin", [AuthController::class, "signin"]);
//         Route::post("/signout", [AuthController::class, "signout"]);
//     });

//     Route::middleware("apiauth")->group(function() {
//         Route::get("/admins", [AdminController::class, "getAdmin"]);
//         Route::post("/users", [AdminController::class, "storeUser"]);
//         Route::get("/users", [AdminController::class, "getUser"]);
//         Route::put("/users/{id}", [AdminController::class, "updateUser"]);
//         Route::delete("/users/{id}", [AdminController::class, "deleteUser"]);

//         Route::get("/games", [GameController::class, "getGames"]);
//         Route::post("/games", [GameController::class, "storeGames"]);
//         Route::get("/games/{slug}", [GameController::class, "showGame"]);
//         Route::post("/games/{slug}/upload", [GameController::class, "upload"]);
//         Route::put("/games/{slug}", [GameController::class, "update"]);
//         Route::delete("/games/{slug}", [GameController::class, "destroy"]);
        
//         Route::get("/users/{username}", [UserController::class, "show"]);

//         Route::get("/games/{slug}/scores", [ScoreController::class, "scores"]);
//         Route::post("/games/{slug}/score", [ScoreController::class, "storeScore"]);
//     });
// });
