<?php

namespace App\Services;

use App\Models\Game;
use App\Models\Battle;
use Illuminate\Support\Facades\Log; // Importação adicionada para possível debug futuro

class ElementalBattleService
{
    private $elementAdvantages = [
        'fogo' => 'terra', // Fogo é forte contra Terra
        'terra' => 'agua', // Terra é forte contra Água
        'agua' => 'fogo',  // Água é forte contra Fogo
        'ar' => 'fogo'     // Ar é forte contra Fogo (Alimenta o fogo)
    ];

    private $phaseEnemies = [
        1 => ['element' => 'terra', 'health' => 20, 'name' => 'Guardião da Terra'],
        2 => ['element' => 'agua', 'health' => 25, 'name' => 'Mestre das Águas'],
        3 => ['element' => 'fogo', 'health' => 30, 'name' => 'Lorde do Fogo'],
        4 => ['element' => 'ar', 'health' => 40, 'name' => 'Deus do Ar']
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

        // CRIA APENAS A PRIMEIRA BATALHA
        $this->createBattleForPhase($game, 1);

        return $game;
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
            'player_health' => $game->player_health,
            'status' => 'active',
            'battle_log' => json_encode([]), // Salva como JSON no banco de dados
        ]);
    }

    /**
     * Processa um ataque do jogador.
     */
    public function attack(Game $game, $attackType)
    {
        // 🛠️ MELHORIA: Busca a batalha ativa explicitamente, conforme o Controller.
        $battle = Battle::where('game_id', $game->id)
                        ->where('phase', $game->current_phase)
                        ->first();

        if (!$battle || $battle->status !== 'active') {
            // Retorna a batalha (pode ser vencida/perdida) ou null se não houver ativa.
            return $battle; 
        }

        // Decodifica o log para manipular como array
        $log = is_string($battle->battle_log) ? json_decode($battle->battle_log, true) : ($battle->battle_log ?? []);
        
        $playerDamage = $this->calculateDamage($game->player_element, $battle->enemy_element, $attackType);
        $enemyDamage = $this->calculateEnemyDamage($battle->enemy_element, $game->player_element);

        // Player attacks
        $battle->enemy_health = max(0, $battle->enemy_health - $playerDamage);
        $log[] = "🎯 Você ataca com **{$attackType}**! Causa **{$playerDamage}** de dano.";

        // Enemy attacks if still alive
        if ($battle->enemy_health > 0) {
            $battle->player_health = max(0, $battle->player_health - $enemyDamage);
            $log[] = "💥 Inimigo contra-ataca! Causa **{$enemyDamage}** de dano.";
        }

        // Check battle result
        if ($battle->enemy_health <= 0) {
            $battle->status = 'won';
            $log[] = "🎉 VOCÊ VENCEU A FASE {$battle->phase}! 🎉";
            
            // Atualiza a vida do jogador e a pontuação no Game após a vitória
            $game->player_health = $battle->player_health;
            $game->score += $battle->phase * 200;
            $game->save();

        } elseif ($battle->player_health <= 0) {
            $battle->status = 'lost';
            $log[] = "💀 Você foi derrotado! Fim de Jogo.";
            
            // Atualiza a vida do jogador no Game para refletir a derrota
            $game->player_health = $battle->player_health; 
            $game->save();
        }

        $battle->battle_log = json_encode($log); // Codifica de volta para salvar
        $battle->save();

        return $battle;
    }

    /**
     * Método estático para verificar se uma fase está disponível.
     */
    public static function isPhaseAvailable(Game $game, $phase)
    {
        // FASE 1: sempre disponível
        if ($phase == 1) return true;
        
        // Para fases 2, 3, 4: verifica se a fase anterior foi vencida
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
        // Verifica se a fase está disponível
        if (!self::isPhaseAvailable($game, $newPhase)) {
            return false;
        }
        
        // Verifica se a nova fase existe no mapeamento de inimigos
        if (!isset($this->phaseEnemies[$newPhase])) {
            return false; 
        }

        // Atualiza a fase atual do jogo
        $game->current_phase = $newPhase;
        $game->save();

        // Tenta encontrar a batalha existente para essa fase
        $existingBattle = Battle::where('game_id', $game->id)
                               ->where('phase', $newPhase)
                               ->first();

        // Se a batalha não existir ou estiver finalizada, cria/reseta
        if (!$existingBattle || $existingBattle->status !== 'active') {
            // Se existir, mas estiver 'won' ou 'lost', reseta
            if ($existingBattle) {
                $enemy = $this->phaseEnemies[$newPhase];
                $existingBattle->update([
                    'enemy_health' => $enemy['health'],
                    'player_health' => $game->player_health, // Importante carregar a vida do Game
                    'status' => 'active',
                    'battle_log' => json_encode([])
                ]);
                return $existingBattle;
            }
            
            // Se não existir, cria uma nova
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
            $multiplier *= 2.0; // Dano dobrado por vantagem
        }

        // Ataque especial
        if ($attackType === 'especial') {
            // Ajustado para refletir a descrição da view (35-45 de dano base)
            $baseDamage = 40; 
            $multiplier *= 1.2; 
        }

        $damage = $baseDamage * $multiplier;

        // Adiciona uma pequena variação de dano
        $damage = $damage * (1 + (rand(-5, 5) / 100)); // Variação de -5% a +5%

        return (int) round($damage);
    }

    /**
     * Calcula o dano do inimigo.
     */
    private function calculateEnemyDamage($enemyElement, $playerElement)
    {
        // 🛠️ MELHORIA: Aumentei o dano base do inimigo para tornar as batalhas mais desafiadoras.
        $baseDamage = 12; // Dano base mais razoável para a vida do jogador de 100
        
        // Vantagem elemental do inimigo
        // O elemento do inimigo tem vantagem sobre o elemento do jogador?
        $isEnemyAdvantage = isset($this->elementAdvantages[$enemyElement]) && 
                            $this->elementAdvantages[$enemyElement] === $playerElement;

        if ($isEnemyAdvantage) {
            $baseDamage *= 1.5; // Inimigo causa 50% a mais de dano
        }
        
        // Adiciona uma pequena variação de dano
        $damage = $baseDamage * (1 + (rand(-10, 10) / 100)); // Variação de -10% a +10%

        return (int) round($damage);
    }

    /**
     * Marca o jogo como completo.
     */
    public function completeGame(Game $game)
    {
        // A checagem para saber se o jogo pode ser completo deve ser feita no Controller
        // (Verificar se a Fase 4 foi vencida).
        
        $game->update([
            'is_completed' => true,
            'score' => $game->score + 1000 // Bônus por completar
        ]);

        return $game;
    }
}