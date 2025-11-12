<?php
namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Battle; // Adicionado para uso explícito no Controller
use App\Services\ElementalBattleService;
use Illuminate\Http\Request;

class GameController extends Controller
{
    private $battleService;

    public function __construct(ElementalBattleService $battleService)
    {
        $this->battleService = $battleService;
    }

    // ... (index e create permanecem iguais) ...
    public function index()
    {
        $games = Game::latest()->get();
        return view('game.index', compact('games'));
    }

    public function create()
    {
        $elements = ['fogo', 'agua', 'terra', 'ar'];
        return view('game.create', compact('elements'));
    }

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

        // O método createNewGame do serviço já cria a primeira batalha internamente.
        // A linha anterior de duplicação foi removida.

        return redirect()->route('game.battle', $game->id);
    }

    // --- SEÇÃO DE BATALHA ---
    public function battle(Game $game)
    {
        // 1. Tenta encontrar a Batalha da fase atual do jogo
        $battle = Battle::where('game_id', $game->id)
                        ->where('phase', $game->current_phase)
                        ->first();
        
        // 2. Se a batalha não existir para a fase atual (o que não deveria ocorrer 
        //    após createNewGame ou changePhase, mas é um bom fallback)
        if (!$battle) {
            $battle = $this->battleService->createBattleForPhase($game, $game->current_phase);
        }

        // Se o jogo foi perdido (vida=0), garantimos que o status da batalha seja 'lost'
        if ($game->player_health <= 0) {
            $battle->status = 'lost';
            // Salva o status, se for a primeira vez que é detectado (opcional)
            if ($battle->getOriginal('status') !== 'lost') {
                $battle->save();
            }
        }
        
        return view('game.battle', compact('game', 'battle'));
    }
    // -----------------------------------

    public function attack(Request $request, Game $game)
    {
        $request->validate([
            'attack_type' => 'required|in:basico,especial'
        ]);

        $battle = $this->battleService->attack($game, $request->attack_type);

        // Verifica se a última batalha vencida foi a Fase 4 para completar o jogo
        if ($battle && $battle->status === 'won' && $battle->phase === 4) {
            $this->battleService->completeGame($game);
            return redirect()->route('game.complete', $game->id);
        }

        return redirect()->route('game.battle', $game->id);
    }

    public function changePhase(Request $request, Game $game)
    {
        $request->validate([
            'phase' => 'required|integer|min:1|max:4'
        ]);

        $newPhase = (int) $request->phase;
        
        // Verifica se a fase é válida e se é a próxima fase
        if (!ElementalBattleService::isPhaseAvailable($game, $newPhase)) {
            return redirect()->route('game.battle', $game->id)->with('error', 'Fase não desbloqueada! Vencça a fase anterior.');
        }

        $battle = $this->battleService->changePhase($game, $newPhase);

        // O método changePhase no serviço retorna false se a fase não estiver disponível.
        if ($battle === false) {
             // Este bloco é um fallback, a validação acima já deve pegar a maioria dos casos.
             return redirect()->route('game.battle', $game->id)->with('error', 'Fase indisponível.');
        }

        return redirect()->route('game.battle', $game->id);
    }

    public function complete(Game $game)
    {
        // Certifica-se de que o jogo foi completo (is_completed) ou venceu a Fase 4
        if (!$game->is_completed) {
             // Você pode querer verificar a última batalha aqui, mas o status 'is_completed'
             // é o marcador final.
             return redirect()->route('game.battle', $game->id);
        }

        return view('game.complete', compact('game'));
    }

    /**
     * MÉTODO ADICIONADO: Exibe a tela de conquistas do jogo.
     */
    public function achievements(Game $game)
    {
        // Retorna a view que você criou, passando os dados do jogo.
        return view('game.achievements', compact('game'));
    }
}