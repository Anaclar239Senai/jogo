<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_name',
        'player_element',
        'player_health',
        'player_level',
        'current_phase',
        'score',
        'is_completed'
    ];

    public function battles()
    {
        return $this->hasMany(Battle::class);
    }

    public function achievements()
    {
        return $this->hasMany(Achievement::class);
    }

    public function currentBattle()
    {
        return $this->battles()
                    ->where('phase', $this->current_phase)
                    ->orWhere('phase', 'final')
                    ->first();
    }

    /**
     * Obtém as conquistas desbloqueadas
     */
    public function unlockedAchievements()
    {
        return $this->achievements()
                    ->whereNotNull('unlocked_at')
                    ->orderBy('unlocked_at', 'desc');
    }

    /**
     * Obtém as conquistas não desbloqueadas
     */
    public function lockedAchievements()
    {
        return $this->achievements()
                    ->whereNull('unlocked_at')
                    ->orderBy('name');
    }

    /**
     * Verifica se uma conquista específica foi desbloqueada
     */
    public function hasAchievement($achievementName)
    {
        return $this->achievements()
                    ->where('name', $achievementName)
                    ->whereNotNull('unlocked_at')
                    ->exists();
    }

    /**
     * Calcula o progresso de conquistas
     */
    public function getAchievementProgress()
    {
        $total = $this->achievements()->count();
        $unlocked = $this->unlockedAchievements()->count();
        
        return [
            'unlocked' => $unlocked,
            'total' => $total,
            'percentage' => $total > 0 ? round(($unlocked / $total) * 100) : 0
        ];
    }

    /**
     * Obtém estatísticas do jogo
     */
    public function getGameStats()
    {
        $battlesWon = $this->battles()->where('status', 'won')->count();
        $battlesLost = $this->battles()->where('status', 'lost')->count();
        $totalBattles = $this->battles()->count();
        
        return [
            'battles_won' => $battlesWon,
            'battles_lost' => $battlesLost,
            'total_battles' => $totalBattles,
            'win_rate' => $totalBattles > 0 ? round(($battlesWon / $totalBattles) * 100) : 0,
            'achievement_progress' => $this->getAchievementProgress(),
            'current_phase' => $this->current_phase,
            'player_health' => $this->player_health,
            'score' => $this->score,
            'is_completed' => $this->is_completed
        ];
    }
}