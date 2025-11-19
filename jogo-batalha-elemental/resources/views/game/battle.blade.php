@extends('layouts.app')

@section('content')

{{-- Decodifica o log da batalha se estiver em formato JSON (string) --}}
@php
    $battleLog = is_string($battle->battle_log) ? json_decode($battle->battle_log, true) : ($battle->battle_log ?? []);

    // Mapeamento de Inimigos (Deve espelhar a lógica do ElementalBattleService)
    $enemyMap = [
        1 => ['name' => 'Guardião da Terra', 'element' => 'terra', 'health' => 25, 'emoji' => '🏔️'],
        2 => ['name' => 'Mestre das Águas', 'element' => 'agua', 'health' => 35, 'emoji' => '🌊'],
        3 => ['name' => 'Lorde do Fogo', 'element' => 'fogo', 'health' => 45, 'emoji' => '🔥'],
        4 => ['name' => 'Deus do Ar', 'element' => 'ar', 'health' => 60, 'emoji' => '🌪️'],
    ];

    $currentEnemy = $enemyMap[$battle->phase] ?? $enemyMap[1]; // Fallback
    $maxEnemyHealth = $currentEnemy['health'];
    $enemyEmoji = $currentEnemy['emoji'];
@endphp

<style>
    /* Animações (Mantidas, pois são essenciais para o estilo) */
    @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.05); } 100% { transform: scale(1); } }
    @keyframes float { 0% { transform: translateY(0px); } 50% { transform: translateY(-10px); } 100% { transform: translateY(0px); } }
    @keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-5px); } 75% { transform: translateX(5px); } }
    .pulse-animation { animation: pulse 2s infinite; }
    .float-animation { animation: float 3s ease-in-out infinite; }
    .shake-animation { animation: shake 0.5s ease-in-out; }
    .victory-glow { box-shadow: 0 0 30px gold, 0 0 60px orange; animation: pulse 1s infinite; }
</style>

<div class="max-w-6xl mx-auto">
    
    {{-- ## ⚔️ Cabeçalho da Batalha --}}
    <div class="bg-gradient-to-r from-purple-900 via-blue-900 to-purple-900 rounded-2xl p-6 mb-6 border-4 border-yellow-400 shadow-2xl">
        <h1 class="text-4xl font-bold text-center text-yellow-300 mb-4">
            @if($battle->phase == 4)
            🌪️ FASE FINAL: {{ $currentEnemy['name'] }} 🌪️
            @else
            ⚡ Fase {{ $battle->phase }} de 4 ⚡
            @endif
        </h1>
        
        <div class="flex justify-between items-center">
            
            {{-- Player Info --}}
            <div class="text-center bg-black bg-opacity-50 p-4 rounded-xl border-2 border-green-500">
                <div class="text-6xl mb-2">
                    @switch($game->player_element)
                        @case('fogo') 🔥 @break
                        @case('agua') 💧 @break
                        @case('terra') 🌿 @break
                        @case('ar') 💨 @break
                    @endswitch
                </div>
                <h2 class="text-xl font-bold text-white">{{ $game->player_name }}</h2>
                <p class="capitalize text-lg text-yellow-300">{{ $game->player_element }}</p>
                
                {{-- Barra de Vida do Jogador --}}
                @php
                    $playerHealth = intval($battle->player_health);
                    $playerHealthPercent = min(100, max(0, $playerHealth)); // A vida máxima é 100
                @endphp
                <div class="w-48 bg-gray-700 rounded-full h-6 mt-2 border-2 border-white">
                    <div class="bg-green-500 h-6 rounded-full transition-all duration-500 flex items-center justify-center" 
                         style="width: {{ $playerHealthPercent }}%">
                        <span class="text-xs font-bold text-white">{{ $playerHealth }}/100</span>
                    </div>
                </div>
            </div>
            
            {{-- VS e Score --}}
            <div class="text-center">
                <div class="text-4xl font-bold text-red-400 pulse-animation">⚔️</div>
                <p class="text-2xl font-bold text-white mt-2">VS</p>
                <div class="bg-black bg-opacity-50 p-3 rounded-lg mt-2">
                    <p class="text-yellow-300 font-bold">Pontos: {{ intval($game->score) }}</p>
                    <p class="text-green-300 text-sm">Fase: {{ $battle->phase }}</p>
                </div>
            </div>

            {{-- Enemy Info --}}
            <div class="text-center bg-black bg-opacity-50 p-4 rounded-xl border-2 border-red-500">
                <div class="text-6xl mb-2 shake-animation">
                    {{ $enemyEmoji }}
                </div>
                <h2 class="text-xl font-bold text-white">
                    {{ $currentEnemy['name'] }}
                </h2>
                <p class="capitalize text-lg text-red-300">{{ $battle->enemy_element }}</p>
                
                {{-- Barra de Vida do Inimigo --}}
                @php
                    $enemyHealth = intval($battle->enemy_health);
                    $enemyHealthPercent = min(100, max(0, ($enemyHealth / $maxEnemyHealth) * 100));
                @endphp
                <div class="w-48 bg-gray-700 rounded-full h-6 mt-2 border-2 border-white">
                    <div class="bg-red-500 h-6 rounded-full transition-all duration-500 flex items-center justify-center" 
                         style="width: {{ $enemyHealthPercent }}%">
                        <span class="text-xs font-bold text-white">{{ $enemyHealth }}/{{ $maxEnemyHealth }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ## 🗺️ Seletor de Fases --}}
    <div class="bg-gradient-to-b from-gray-800 to-gray-900 rounded-2xl p-6 mb-6 border-4 border-purple-500">
        <h3 class="text-2xl font-bold text-center text-yellow-300 mb-4">🗺️ SELECIONE SUA FASE 🗺️</h3>
        <div class="grid grid-cols-4 gap-4">
            @for($i = 1; $i <= 4; $i++)
                @php
                    // Chama o método estático do serviço para verificar se a fase está liberada
                    $isAvailable = \App\Services\ElementalBattleService::isPhaseAvailable($game, $i);
                    $isCurrent = $i == $game->current_phase;
                    $phaseData = $enemyMap[$i]; // Usa o mapa de inimigos criado acima
                @endphp
                <form action="{{ route('game.change-phase', $game->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="phase" value="{{ $i }}">
                    <button type="submit" 
                            class="w-full p-4 rounded-lg text-center transition-all duration-300 transform hover:scale-105
                                @if($isCurrent) bg-yellow-600 border-2 border-yellow-400 text-white
                                @elseif($isAvailable) bg-green-600 border-2 border-green-400 text-white hover:bg-green-700
                                @else bg-gray-600 border-2 border-gray-400 text-gray-400 cursor-not-allowed @endif"
                            @if(!$isAvailable) disabled @endif>
                        <div class="text-2xl mb-2">{{ $phaseData['emoji'] }}</div>
                        <div class="font-bold">Fase {{ $i }}</div>
                        <div class="text-xs mt-1 capitalize">{{ $phaseData['element'] }}</div>
                        @if(!$isAvailable)
                        <div class="text-xs mt-1">🔒 Vença a anterior</div>
                        @endif
                    </button>
                </form>
            @endfor
        </div>
    </div>

    {{-- ## 📜 Histórico da Batalha --}}
    @if(count($battleLog) > 0)
    <div class="bg-gradient-to-b from-gray-800 to-gray-900 rounded-xl p-4 mb-6 max-h-48 overflow-y-auto border-2 border-blue-400">
        <h3 class="font-bold mb-3 text-lg text-center text-yellow-300">📜 HISTÓRICO DA BATALHA 📜</h3>
        <div class="space-y-2">
            {{-- Exibe apenas as últimas 8 entradas --}}
            @foreach(array_slice($battleLog, -8) as $log)
            <div class="flex items-center space-x-3 p-3 bg-gray-700 rounded-lg border-l-4 
                @if(str_contains($log, 'VENCEU')) border-green-500 bg-green-900
                @elseif(str_contains($log, 'derrotado')) border-red-500 bg-red-900
                @elseif(str_contains($log, 'Você ataca')) border-yellow-500
                @elseif(str_contains($log, 'Inimigo')) border-red-400
                @else border-blue-400 @endif">
                <span class="text-lg">
                    @if(str_contains($log, 'VENCEU')) 🎉
                    @elseif(str_contains($log, 'derrotado')) 💀
                    @elseif(str_contains($log, 'Você ataca')) ⚡
                    @elseif(str_contains($log, 'Inimigo')) 🎯
                    @else 📝
                    @endif
                </span>
                <p class="text-sm font-semibold flex-1 
                    @if(str_contains($log, 'VENCEU')) text-green-300
                    @elseif(str_contains($log, 'derrotado')) text-red-300
                    @elseif(str_contains($log, 'Você ataca')) text-yellow-200
                    @elseif(str_contains($log, 'Inimigo')) text-red-200
                    @else text-white @endif">
                    {{ $log }}
                </p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ## 🎯 Ações de Batalha --}}
    @if($battle->status === 'active')
    <div class="bg-gradient-to-b from-gray-800 to-gray-900 rounded-2xl p-8 mb-6 border-4 border-green-500 shadow-xl">
        <h3 class="text-2xl font-bold text-center mb-6 text-yellow-300">🎯 ESCOLHA SEU ATAQUE PODEROSO!</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Ataque Básico --}}
            <form action="{{ route('game.attack', $game->id) }}" method="POST">
                @csrf
                <input type="hidden" name="attack_type" value="basico">
                <button type="submit" 
                        class="w-full h-32 bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-600 hover:to-orange-600 text-white font-bold rounded-2xl text-xl transition-all duration-300 transform hover:scale-105 hover:shadow-2xl border-4 border-yellow-300 flex flex-col items-center justify-center pulse-animation">
                    <span class="text-4xl mb-2">⚔️</span>
                    <span class="font-bold">ATAQUE BÁSICO</span>
                    <span class="text-yellow-200 text-sm mt-1">Dano Base: ~25</span>
                </button>
            </form>
            
            {{-- Ataque Especial --}}
            <form action="{{ route('game.attack', $game->id) }}" method="POST">
                @csrf
                <input type="hidden" name="attack_type" value="especial">
                <button type="submit" 
                        class="w-full h-32 bg-gradient-to-r from-red-500 to-pink-500 hover:from-red-600 hover:to-pink-600 text-white font-bold rounded-2xl text-xl transition-all duration-300 transform hover:scale-105 hover:shadow-2xl border-4 border-red-300 flex flex-col items-center justify-center float-animation">
                    <span class="text-4xl mb-2">💥</span>
                    <span class="font-bold">ATAQUE ESPECIAL</span>
                    <span class="text-red-200 text-sm mt-1">Dano Alto: ~45 (Potencial de 90 com Vantagem)</span>
                </button>
            </form>
        </div>

        {{-- Dica de Vantagem --}}
        <div class="mt-6 p-4 bg-blue-900 rounded-xl border-2 border-blue-400 text-center">
            <p class="text-blue-200 font-semibold text-lg">
                🎊 <strong>DICA:</strong> Vantagem Elemental dobra seu dano! Use o **Ataque Especial** para o máximo impacto! 🎊
            </p>
        </div>
    </div>

    @elseif($battle->status === 'won')
    {{-- ## 🎉 Tela de Vitória --}}
    <div class="text-center bg-gradient-to-b from-green-900 to-yellow-900 rounded-2xl p-8 mb-6 border-4 border-yellow-400 victory-glow">
        <div class="text-6xl mb-4">🎉🏆🎊</div>
        <h2 class="text-4xl font-bold mb-4 text-yellow-300">VITÓRIA ESPETACULAR!</h2>
        
        <div class="bg-black bg-opacity-50 p-6 rounded-xl mb-6">
            <p class="text-2xl mb-3 text-white">Você derrotou o 
                <span class="text-red-300 font-bold">
                    {{ $currentEnemy['name'] }} {{ $enemyEmoji }}
                </span>
            </p>
            <p class="text-3xl text-yellow-300 font-bold mb-2">+{{ intval($battle->phase) * 200 }} PONTOS! 💰</p>
            <p class="text-lg text-green-300">Vida restante: {{ intval($battle->player_health) }}/100 ❤️</p>
        </div>
        
        @if($battle->phase == 4)
        <div class="bg-gradient-to-r from-purple-600 to-pink-600 p-6 rounded-xl mb-6">
            <p class="text-3xl font-bold text-white mb-4">🏆 CAMPEÃO DOS ELEMENTOS! 🏆</p>
            <p class="text-xl text-yellow-200">Você dominou todos os 4 elementos e se tornou uma lenda!</p>
        </div>
        <a href="{{ url('/game/' . $game->id . '/achievements') }}" 
            class="bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300 transform hover:scale-105 inline-flex items-center">
             <i class="fas fa-trophy mr-2"></i>
             VER CONQUISTAS 🌬
         </a>
        @else
        <a href="{{ route('game.battle', $game->id) }}" 
           class="inline-block bg-gradient-to-r from-green-500 to-blue-500 hover:from-green-600 hover:to-blue-600 text-white font-bold py-4 px-12 rounded-2xl text-2xl transition-all duration-300 transform hover:scale-110 shadow-2xl">
            ⚡ PRÓXIMA FASE INCRÍVEL ⚡
        </a>
        @endif

        {{-- Fogos de artifício --}}
        <div class="mt-6 text-4xl">
            ✨⭐🌟💫🎇🎆✨
        </div>
    </div>

    @else
    {{-- ## 💀 Tela de Derrota --}}
    <div class="text-center bg-gradient-to-b from-red-900 to-gray-900 rounded-2xl p-8 mb-6 border-4 border-red-600">
        <div class="text-6xl mb-4">💀😵⚰️</div>
        <h2 class="text-4xl font-bold mb-4 text-red-300">QUE AZAR!</h2>
        <p class="text-xl text-white mb-6">Até mesmo os melhores heróis têm dias ruins...</p>
        
        <div class="space-y-4">
            <a href="{{ route('game.create') }}" 
               class="block bg-gradient-to-r from-red-500 to-orange-500 hover:from-red-600 hover:to-orange-600 text-white font-bold py-4 px-8 rounded-xl text-xl transition-all duration-300 transform hover:scale-105">
                🎮 REVANCHE GLORIOSA!
            </a>
            <a href="{{ route('game.index') }}" 
               class="block bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-lg transition-all duration-200">
                📋 Voltar ao Início
            </a>
        </div>
    </div>
    @endif

    {{-- ## 🌍 Guia de Elementos --}}
    <div class="bg-gradient-to-b from-purple-900 to-blue-900 rounded-2xl p-6 border-4 border-indigo-400">
        <h3 class="font-bold mb-4 text-2xl text-center text-yellow-300">🌍 GUIA DOS ELEMENTOS PODEROSOS</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            {{-- FOGO vs TERRA vs ÁGUA (Mantido) --}}
            <div class="text-center p-4 bg-red-900 rounded-xl border-2 border-red-400 transform hover:scale-105 transition-all duration-300">
                <div class="text-3xl mb-2">🔥</div>
                <strong class="text-lg text-white">FOGO</strong>
                <p class="mt-2 text-yellow-200">🔥 Queima a 🌿 Terra</p>
                <p class="text-blue-200">💧 Água apaga o 🔥 Fogo</p>
            </div>
            {{-- ÁGUA vs FOGO vs TERRA (Mantido) --}}
            <div class="text-center p-4 bg-blue-900 rounded-xl border-2 border-blue-400 transform hover:scale-105 transition-all duration-300">
                <div class="text-3xl mb-2">💧</div>
                <strong class="text-lg text-white">ÁGUA</strong>
                <p class="mt-2 text-yellow-200">💧 Apaga o 🔥 Fogo</p>
                <p class="text-green-200">🌿 Terra absorve 💧 Água</p>
            </div>
            {{-- TERRA vs ÁGUA vs FOGO (Mantido) --}}
            <div class="text-center p-4 bg-green-900 rounded-xl border-2 border-green-400 transform hover:scale-105 transition-all duration-300">
                <div class="text-3xl mb-2">🌿</div>
                <strong class="text-lg text-white">TERRA</strong>
                <p class="mt-2 text-yellow-200">🌿 Absorve 💧 Água</p>
                <p class="text-red-200">🔥 Fogo queima 🌿 Terra</p>
            </div>
            {{-- AR vs FOGO (Mantido) --}}
            <div class="text-center p-4 bg-gray-800 rounded-xl border-2 border-gray-400 transform hover:scale-105 transition-all duration-300">
                <div class="text-3xl mb-2">💨</div>
                <strong class="text-lg text-white">AR</strong>
                <p class="mt-2 text-yellow-200">💨 Alimenta 🔥 Fogo</p>
                <p class="text-white">⚖️ Neutro contra outros</p>
            </div>
        </div>
    </div>
</div>

{{-- Script para Animações Especiais --}}
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Scroll automático para o histórico
    const battleLogElement = document.querySelector('.max-h-48');
    if (battleLogElement) {
        battleLogElement.scrollTop = battleLogElement.scrollHeight;
    }
    
    // Efeitos de vitória
    @if($battle->status === 'won')
    // Adicionei a verificação da função confetti() para evitar erros se a CDN falhar
    setTimeout(function() {
        if (typeof confetti === 'function') {
            // Explosão central
            confetti({ particleCount: 150, spread: 70, origin: { y: 0.6 }, colors: ['#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff'] });
            
            // Explosão lateral esquerda
            setTimeout(function() { confetti({ particleCount: 100, angle: 60, spread: 55, origin: { x: 0 }, colors: ['#ff0000', '#00ff00', '#0000ff'] }); }, 250);
            
            // Explosão lateral direita
            setTimeout(function() { confetti({ particleCount: 100, angle: 120, spread: 55, origin: { x: 1 }, colors: ['#ffff00', '#ff00ff', '#00ffff'] }); }, 500);
        }
    }, 1000);
    @endif
});
</script>

@endsection