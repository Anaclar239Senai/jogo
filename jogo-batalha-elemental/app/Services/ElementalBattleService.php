<?php

namespace App\Services;

use App\Models\Game;
use App\Models\Battle;
use App\Models\Achievement;
use Illuminate\Support\Facades\Log;

class ElementalBattleService
{
    private $elementAdvantages = [
        'fogo' => 'terra',
        'terra' => 'agua', 
        'agua' => 'fogo',
        'ar' => 'fogo'
    ];

    private $phaseEnemies = [
        1 => ['element' => 'terra', 'health' => 25, 'name' => 'Guardião da Terra', 'base_damage' => 8],
        2 => ['element' => 'agua', 'health' => 35, 'name' => 'Mestre das Águas', 'base_damage' => 10],
        3 => ['element' => 'fogo', 'health' => 45, 'name' => 'Lorde do Fogo', 'base_damage' => 12],
        4 => ['element' => 'ar', 'health' => 60, 'name' => 'Deus do Ar', 'base_damage' => 15]
    ];

    /**
     * Cria um novo jogo e a primeira batalha.
     */
    public function createNewGame($playerName, $playerElement)
    {
        $game = Game::create([
            'player_name' => $playerName,
            'player_element' => $playerElement,
            'player_health' => 100,
            'current_phase' => 1,
            'score' => 0,
        ]);

        $this->createBattleForPhase($game, 1);
        $this->createAchievements($game); // Cria as conquistas para este jogo

        return $game;
    }

    /**
     * Cria conquistas para um jogo
     */
    public function createAchievements(Game $game)
    {
        $achievements = [
            [
                'name' => 'Primeiro Passo',
                'description' => 'Derrote o Guardião da Terra',
                'icon' => '🌱'
            ],
            [
                'name' => 'Mestre das Águas', 
                'description' => 'Derrote o Mestre das Águas',
                'icon' => '💧'
            ],
            [
                'name' => 'Domador de Chamas',
                'description' => 'Derrote o Lorde do Fogo',
                'icon' => '🔥'
            ],
            [
                'name' => 'Lenda Elemental',
                'description' => 'Derrote o Deus do Ar e complete o jogo',
                'icon' => '🌪️'
            ],
            [
                'name' => 'Estratégia Pura',
                'description' => 'Complete o jogo com mais de 50% de vida',
                'icon' => '🎯'
            ],
            [
                'name' => 'Invencível',
                'description' => 'Complete o jogo sem perder nenhuma batalha',
                'icon' => '🛡️'
            ]
        ];

        foreach ($achievements as $achievement) {
            Achievement::create([
                'game_id' => $game->id,
                'name' => $achievement['name'],
                'description' => $achievement['description'],
                'icon' => $achievement['icon']
            ]);
        }
    }

    /**
     * Cria uma nova batalha para a fase especificada.
     */
    public function createBattleForPhase(Game $game, $phase)
    {
        $enemy = $this->phaseEnemies[$phase];

        return Battle::create([
            'game_id' => $game->id,
            'phase' => $phase,
            'enemy_element' => $enemy['element'],
            'enemy_health' => $enemy['health'],
            'enemy_max_health' => $enemy['health'],
            'player_health' => $game->player_health,
            'player_max_health' => 100,
            'status' => 'active',
            'battle_log' => json_encode([]),
        ]);
    }

    /**
     * Processa um ataque do jogador.
     */
    public function attack(Game $game, $attackType)
    {
        $battle = Battle::where('game_id', $game->id)
                        ->where('phase', $game->current_phase)
                        ->first();

        if (!$battle || $battle->status !== 'active') {
            return $battle; 
        }

        $log = is_string($battle->battle_log) ? json_decode($battle->battle_log, true) : ($battle->battle_log ?? []);
        
        $playerDamage = $this->calculateDamage($game->player_element, $battle->enemy_element, $attackType);
        $enemyDamage = $this->calculateEnemyDamage($battle->enemy_element, $game->player_element, $game->current_phase);

        // Player attacks
        $battle->enemy_health = max(0, $battle->enemy_health - $playerDamage);
        $log[] = "🎯 Você ataca com **{$attackType}**! Causa **{$playerDamage}** de dano.";

        // Enemy attacks if still alive
        if ($battle->enemy_health > 0) {
            $battle->player_health = max(0, $battle->player_health - $enemyDamage);
            $log[] = "💥 Inimigo contra-ataca! Causa **{$enemyDamage}** de dano.";
            
            // Chance de ataque duplo do inimigo
            $doubleAttackChance = min(20 + ($game->current_phase * 5), 40);
            if (rand(1, 100) <= $doubleAttackChance) {
                $doubleDamage = $this->calculateEnemyDamage($battle->enemy_element, $game->player_element, $game->current_phase);
                $battle->player_health = max(0, $battle->player_health - $doubleDamage);
                $log[] = "⚡ **ATAQUE DUPLO DO INIMIGO!** Causa **{$doubleDamage}** de dano adicional!";
            }
        }

        // Check battle result
        if ($battle->enemy_health <= 0) {
            $battle->status = 'won';
            $log[] = "🎉 VOCÊ VENCEU A FASE {$battle->phase}! 🎉";
            
            $game->player_health = $battle->player_health;
            $game->score += $battle->phase * 200;
            $game->save();

            // Verifica conquistas de fase
            $this->checkPhaseAchievements($game, $battle->phase);

        } elseif ($battle->player_health <= 0) {
            $battle->status = 'lost';
            $log[] = "💀 Você foi derrotado! Fim de Jogo.";
            
            $game->player_health = 0;
            $game->save();
        }

        $battle->battle_log = json_encode($log);
        $battle->save();

        return $battle;
    }

    /**
     * Verifica e desbloqueia conquistas de fase
     */
    private function checkPhaseAchievements(Game $game, $phase)
    {
        $achievementNames = [
            1 => 'Primeiro Passo',
            2 => 'Mestre das Águas',
            3 => 'Domador de Chamas',
            4 => 'Lenda Elemental'
        ];

        if (isset($achievementNames[$phase])) {
            $this->unlockAchievement($game, $achievementNames[$phase]);
        }

        // Se completou a fase 4, verifica outras conquistas
        if ($phase === 4) {
            $this->checkCompletionAchievements($game);
        }
    }

    /**
     * Verifica conquistas de conclusão do jogo
     */
    private function checkCompletionAchievements(Game $game)
    {
        // Conquista: Estratégia Pura (mais de 50% de vida)
        if ($game->player_health > 50) {
            $this->unlockAchievement($game, 'Estratégia Pura');
        }

        // Conquista: Invencível (sem perder batalhas)
        $lostBattles = Battle::where('game_id', $game->id)
                            ->where('status', 'lost')
                            ->count();
        
        if ($lostBattles === 0) {
            $this->unlockAchievement($game, 'Invencível');
        }

        // Marca o jogo como completo
        $this->completeGame($game);
    }

    /**
     * Desbloqueia uma conquista específica
     */
    private function unlockAchievement(Game $game, $achievementName)
    {
        $achievement = Achievement::where('game_id', $game->id)
                                 ->where('name', $achievementName)
                                 ->whereNull('unlocked_at')
                                 ->first();

        if ($achievement) {
            $achievement->update([
                'unlocked_at' => now()
            ]);
            
            Log::info("Conquista desbloqueada: {$achievementName} para o jogo {$game->id}");
        }
    }

    /**
     * Método estático para verificar se uma fase está disponível.
     */
    public static function isPhaseAvailable(Game $game, $phase)
    {
        if ($phase == 1) return true;
        
        $previousPhase = $phase - 1;
        $previousBattle = Battle::where('game_id', $game->id)
                               ->where('phase', $previousPhase)
                               ->where('status', 'won')
                               ->first();
        
        return $previousBattle !== null;
    }

    /**
     * Tenta mudar o jogo para uma nova fase.
     */
    public function changePhase(Game $game, $newPhase)
    {
        if (!self::isPhaseAvailable($game, $newPhase)) {
            return false;
        }
        
        if (!isset($this->phaseEnemies[$newPhase])) {
            return false; 
        }

        $game->current_phase = $newPhase;
        $game->save();

        $existingBattle = Battle::where('game_id', $game->id)
                               ->where('phase', $newPhase)
                               ->first();

        if (!$existingBattle || $existingBattle->status !== 'active') {
            if ($existingBattle) {
                $enemy = $this->phaseEnemies[$newPhase];
                $existingBattle->update([
                    'enemy_element' => $enemy['element'],
                    'enemy_health' => $enemy['health'],
                    'enemy_max_health' => $enemy['health'],
                    'player_health' => $game->player_health,
                    'player_max_health' => 100,
                    'status' => 'active',
                    'battle_log' => json_encode([])
                ]);
                return $existingBattle;
            }
            
            return $this->createBattleForPhase($game, $newPhase);
        }

        return $existingBattle;
    }

    /**
     * Calcula o dano do jogador.
     */
    private function calculateDamage($playerElement, $enemyElement, $attackType)
    {
        $baseDamage = 25;
        $multiplier = 1.0;
        
        // Vantagem elemental
        if (isset($this->elementAdvantages[$playerElement]) && 
            $this->elementAdvantages[$playerElement] === $enemyElement) {
            $multiplier *= 2.0;
        }

        // Ataque especial
        if ($attackType === 'especial') {
            $baseDamage = 40; 
            $multiplier *= 1.2; 
        }

        $damage = $baseDamage * $multiplier;
        $damage = $damage * (1 + (rand(-8, 8) / 100));

        return (int) round($damage);
    }

    /**
     * Calcula o dano do inimigo.
     */
    private function calculateEnemyDamage($enemyElement, $playerElement, $phase)
    {
        $enemyConfig = $this->phaseEnemies[$phase];
        $baseDamage = $enemyConfig['base_damage'];
        
        // Bônus de dano por fase
        $phaseBonus = ($phase - 1) * 2;
        $baseDamage += $phaseBonus;

        // Vantagem elemental do inimigo
        $isEnemyAdvantage = isset($this->elementAdvantages[$enemyElement]) && 
                            $this->elementAdvantages[$enemyElement] === $playerElement;

        if ($isEnemyAdvantage) {
            $baseDamage *= 1.8;
        }
        
        // Chance de crítico do inimigo
        $criticalChance = min(15 + ($phase * 3), 30);
        if (rand(1, 100) <= $criticalChance) {
            $baseDamage *= 1.5;
        }
        
        $damage = $baseDamage * (1 + (rand(-15, 15) / 100));

        return (int) round($damage);
    }

    /**
     * Marca o jogo como completo.
     */
    public function completeGame(Game $game)
    {
        $game->update([
            'is_completed' => true,
            'score' => $game->score + 1000
        ]);

        return $game;
    }

    /**
     * Reseta todas as batalhas do jogo (para debug/correção)
     */
    public function resetAllBattles(Game $game)
    {
        foreach ([1, 2, 3, 4] as $phase) {
            $battle = Battle::where('game_id', $game->id)
                           ->where('phase', $phase)
                           ->first();
            
            if ($battle) {
                $enemy = $this->phaseEnemies[$phase];
                $battle->update([
                    'enemy_element' => $enemy['element'],
                    'enemy_health' => $enemy['health'],
                    'enemy_max_health' => $enemy['health'],
                    'player_health' => $game->player_health,
                    'player_max_health' => 100,
                    'status' => ($phase == $game->current_phase) ? 'active' : $battle->status,
                    'battle_log' => json_encode([])
                ]);
            } else {
                $this->createBattleForPhase($game, $phase);
            }
        }
        
        return true;
    }

    /**
     * Obtém informações do inimigo atual
     */
    public function getCurrentEnemyInfo(Game $game)
    {
        $phase = $game->current_phase;
        if (isset($this->phaseEnemies[$phase])) {
            return $this->phaseEnemies[$phase];
        }
        return null;
    }

    /**
     * Obtém todas as conquistas de um jogo
     */
    public function getAchievements(Game $game)
    {
        return Achievement::where('game_id', $game->id)->get();
    }

    /**
     * Obtém estatísticas do jogo para conquistas
     */
    public function getGameStats(Game $game)
    {
        $totalBattles = Battle::where('game_id', $game->id)->count();
        $wonBattles = Battle::where('game_id', $game->id)->where('status', 'won')->count();
        $lostBattles = Battle::where('game_id', $game->id)->where('status', 'lost')->count();
        
        return [
            'total_battles' => $totalBattles,
            'won_battles' => $wonBattles,
            'lost_battles' => $lostBattles,
            'win_rate' => $totalBattles > 0 ? ($wonBattles / $totalBattles) * 100 : 0,
            'player_health' => $game->player_health,
            'score' => $game->score,
            'is_completed' => $game->is_completed
        ];
    }

    /**
 * Corrige todas as batalhas existentes com os valores atualizados de vida dos inimigos
 */
public function fixAllBattlesHealth()
{
    $updatedCount = 0;
    
    foreach ([1, 2, 3, 4] as $phase) {
        if (isset($this->phaseEnemies[$phase])) {
            $enemy = $this->phaseEnemies[$phase];
            $count = Battle::where('phase', $phase)
                         ->update([
                             'enemy_health' => $enemy['health'],
                             'enemy_max_health' => $enemy['health']
                         ]);
            $updatedCount += $count;
            Log::info("Fase {$phase}: {$count} batalhas atualizadas para vida {$enemy['health']}");
        }
    }
    
    return $updatedCount;
}
}