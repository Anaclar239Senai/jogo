<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Battle;
use App\Services\ElementalBattleService;
use Illuminate\Http\Request;

class GameController extends Controller
{
    private $battleService;

    public function __construct(ElementalBattleService $battleService)
    {
        $this->battleService = $battleService;
    }

    /**
     * Exibe a página inicial com lista de jogos
     */
    public function index()
    {
        $games = Game::latest()->get();
        return view('game.index', compact('games'));
    }

    /**
     * Exibe o formulário de criação de novo jogo
     */
    public function create()
    {
        $elements = ['fogo', 'agua', 'terra', 'ar'];
        return view('game.create', compact('elements'));
    }

    /**
     * Processa a criação de um novo jogo
     */
    public function store(Request $request)
    {
        $request->validate([
            'player_name' => 'required|string|max:50',
            'player_element' => 'required|in:fogo,agua,terra,ar'
        ]);

        $game = $this->battleService->createNewGame(
            $request->player_name,
            $request->player_element
        );

        return redirect()->route('game.battle', $game->id);
    }

    /**
     * Exibe a tela de batalha atual do jogo
     */
    public function battle(Game $game)
    {
        // Tenta encontrar a Batalha da fase atual do jogo
        $battle = Battle::where('game_id', $game->id)
                        ->where('phase', $game->current_phase)
                        ->first();
        
        // Se a batalha não existir para a fase atual, cria uma nova
        if (!$battle) {
            $battle = $this->battleService->createBattleForPhase($game, $game->current_phase);
        }

        // Se o jogo foi perdido (vida=0), atualiza o status da batalha
        if ($game->player_health <= 0 && $battle->status !== 'lost') {
            $battle->status = 'lost';
            $battle->save();
        }
        
        return view('game.battle', compact('game', 'battle'));
    }

    /**
     * Processa um ataque do jogador
     */
    public function attack(Request $request, Game $game)
    {
        $request->validate([
            'attack_type' => 'required|in:basico,especial'
        ]);

        $battle = $this->battleService->attack($game, $request->attack_type);

        // Se o jogador perdeu a batalha
        if ($battle && $battle->status === 'lost') {
            return redirect()->route('game.battle', $game->id);
        }

        // Se o jogador venceu a fase 4, completa o jogo
        if ($battle && $battle->status === 'won' && $battle->phase === 4) {
            $this->battleService->completeGame($game);
            return redirect()->route('game.complete', $game->id);
        }

        return redirect()->route('game.battle', $game->id);
    }

    /**
     * Processa a mudança de fase
     */
    public function changePhase(Request $request, Game $game)
    {
        $request->validate([
            'phase' => 'required|integer|min:1|max:4'
        ]);

        $newPhase = (int) $request->phase;
        
        // Verifica se a fase está disponível
        if (!ElementalBattleService::isPhaseAvailable($game, $newPhase)) {
            return redirect()->route('game.battle', $game->id)
                           ->with('error', 'Fase não desbloqueada! Vença a fase anterior.');
        }

        $battle = $this->battleService->changePhase($game, $newPhase);

        if ($battle === false) {
            return redirect()->route('game.battle', $game->id)
                           ->with('error', 'Fase indisponível.');
        }

        return redirect()->route('game.battle', $game->id)
                       ->with('success', 'Fase ' . $newPhase . ' iniciada!');
    }

    /**
     * Exibe a tela de conclusão do jogo
     */
    public function complete(Game $game)
    {
        // Verifica se o jogo foi realmente completado
        if (!$game->is_completed) {
            // Se não foi completado, verifica se venceu a fase 4
            $phase4Battle = Battle::where('game_id', $game->id)
                                ->where('phase', 4)
                                ->where('status', 'won')
                                ->first();
            
            if ($phase4Battle) {
                $this->battleService->completeGame($game);
            } else {
                return redirect()->route('game.battle', $game->id);
            }
        }

        return view('game.complete', compact('game'));
    }

    /**
     * Exibe a tela de conquistas do jogo
     */
    public function achievements(Game $game)
    {
        return view('game.achievements', compact('game'));
    }

    /**
     * MÉTODO ADICIONADO: Exibe detalhes do jogo (para a rota GET /game/{game})
     */
    public function show(Game $game)
    {
        // Redireciona para a batalha atual do jogo
        return redirect()->route('game.battle', $game->id);
    }

    /**
     * MÉTODO ADICIONADO: Reinicia um jogo existente
     */
    public function restart(Game $game)
    {
        // Reseta todas as batalhas do jogo
        $this->battleService->resetAllBattles($game);
        
        // Reseta o jogo
        $game->update([
            'player_health' => 100,
            'current_phase' => 1,
            'score' => 0,
            'is_completed' => false
        ]);

        return redirect()->route('game.battle', $game->id)
                       ->with('success', 'Jogo reiniciado com sucesso!');
    }

    /**
     * MÉTODO ADICIONADO: Exclui um jogo
     */
    public function destroy(Game $game)
    {
        $game->delete();
        
        return redirect()->route('game.index')
                       ->with('success', 'Jogo excluído com sucesso!');
    }
}