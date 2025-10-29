<?php

use App\Http\Controllers\GameController;
use Illuminate\Support\Facades\Route;

Route::get('/', [GameController::class, 'index'])->name('game.index');
Route::get('/game/create', [GameController::class, 'create'])->name('game.create');
Route::post('/game', [GameController::class, 'store'])->name('game.store');
Route::get('/game/{game}/battle', [GameController::class, 'battle'])->name('game.battle');
Route::post('/game/{game}/attack', [GameController::class, 'attack'])->name('game.attack');

// Rota das Conquistas (deve ter prioridade sobre 'complete')
Route::get('/game/{game}/achievements', [GameController::class, 'achievements'])->name('game.achievements');

Route::get('/game/{game}/complete', [GameController::class, 'complete'])->name('game.complete');
Route::post('/game/{game}/change-phase', [GameController::class, 'changePhase'])->name('game.change-phase');
