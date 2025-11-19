<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;

class AchievementController extends Controller
{
    public function index($gameId)
    {
        $game = Game::with(['achievements' => function($query) {
            $query->orderBy('unlocked_at', 'desc')->orderBy('name');
        }])->findOrFail($gameId);
        
        // CORRIJA ESTA LINHA - adicione 'game.' antes do nome da view
        return view('game.achievements', [
            'game' => $game
        ]);
    }
}