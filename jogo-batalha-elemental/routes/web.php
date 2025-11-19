<?php

use App\Http\Controllers\GameController;
use App\Http\Controllers\AchievementController;
use Illuminate\Support\Facades\Route;

Route::get('/', [GameController::class, 'index'])->name('game.index');
Route::get('/game/create', [GameController::class, 'create'])->name('game.create');
Route::post('/game', [GameController::class, 'store'])->name('game.store');
Route::get('/game/{game}', [GameController::class, 'show'])->name('game.show'); // ← ADICIONE ESTA LINHA
Route::get('/game/{game}/battle', [GameController::class, 'battle'])->name('game.battle');
Route::post('/game/{game}/attack', [GameController::class, 'attack'])->name('game.attack');
Route::get('/game/{game}/achievements', [AchievementController::class, 'index'])->name('game.achievements');
Route::get('/game/{game}/complete', [GameController::class, 'complete'])->name('game.complete');
Route::post('/game/{game}/change-phase', [GameController::class, 'changePhase'])->name('game.change-phase');

// Rota temporária para corrigir a vida dos inimigos
Route::get('/fix-enemy-health', function() {
    $battleService = app(App\Services\ElementalBattleService::class);
    $count = $battleService->fixAllBattlesHealth();
    return "✅ {$count} batalhas corrigidas! A vida dos inimigos agora está consistente.";
});