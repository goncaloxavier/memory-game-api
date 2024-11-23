<?php

use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\GameController;

// Add this POST route for login
Route::post('/login', [UserController::class, 'login']);


Route::get('/users/{id}/games', [GameController::class, 'getUserGames']);

Route::get('/games/leaderboard/global', [GameController::class, 'getGlobalLeaderboard']);
Route::get('/games/leaderboard/personal/{id}', [GameController::class, 'getPersonalLeaderboard']);

// Add a POST route to save game data
Route::post('/games/save', [GameController::class, 'saveGame']);