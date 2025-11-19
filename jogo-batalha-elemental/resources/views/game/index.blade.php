@extends('layouts.app')

@section('content')
<div class="min-h-screen relative bg-gradient-to-b from-gray-900 to-black">
    <!-- Header Épico -->
    <div class="relative overflow-hidden border-b-4 border-red-900 bg-gradient-to-b from-red-900 to-black py-16 mb-12">
        <div class="absolute inset-0 bg-black opacity-60"></div>
        <div class="relative text-center">
            <h1 class="text-6xl font-bold medieval-text blood-text flame-animation mb-6">
                ⚔️ GUARDIÃO DE ELARION ⚔️
            </h1>
            <p class="text-2xl text-yellow-200 medieval-text">
                Um mundo onde apenas os fortes sobrevivem
            </p>
            <div class="mt-8 text-4xl text-red-400">
                🗡️ 🛡️ 🏹 🔥
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4">
        <!-- Botão Iniciar Jornada -->
        <div class="text-center mb-16">
            <a href="{{ route('game.create') }}" 
               class="inline-block medieval-text bg-gradient-to-r from-red-800 to-yellow-800 hover:from-red-900 hover:to-yellow-900 text-yellow-200 font-bold text-2xl py-6 px-16 rounded-lg transition-all duration-300 transform hover:scale-105 hover:shadow-2xl border-2 border-yellow-700 pulse-dark relative overflow-hidden group">
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-yellow-600 to-transparent transform -skew-x-12 -translate-x-full group-hover:translate-x-full transition-all duration-1000"></div>
                <span class="text-3xl">🗡️</span>
                <span class="ml-4 relative z-10">INICIAR JORNADA</span>
                <span class="text-3xl ml-4">🛡️</span>
                <div class="text-sm text-red-300 mt-2 relative z-10">A escuridão aguarda...</div>
            </a>
        </div>

        <!-- Elementos no Estilo Dark Fantasy -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-16">
            <!-- Fogo -->
            <div class="text-center ancient-parchment p-6 rounded-lg border-2 border-red-700 transform hover:scale-105 transition-all duration-300 hover:border-red-500">
                <div class="text-4xl mb-3 flame-animation">🔥</div>
                <h3 class="text-xl font-bold blood-text medieval-text">FOGO DA PERDIÇÃO</h3>
                <p class="text-yellow-200 mt-2 text-sm">Consome tudo em seu caminho</p>
                <p class="text-red-300 text-xs mt-1">Fraqueza: Água</p>
            </div>
            
            <!-- Água -->
            <div class="text-center ancient-parchment p-6 rounded-lg border-2 border-blue-700 transform hover:scale-105 transition-all duration-300 hover:border-blue-500">
                <div class="text-4xl mb-3 water-animation">💧</div>
                <h3 class="text-xl font-bold text-blue-300 medieval-text">ÁGUA PROFUNDA</h3>
                <p class="text-blue-200 mt-2 text-sm">Adaptável e implacável</p>
                <p class="text-blue-300 text-xs mt-1">Fraqueza: Terra</p>
            </div>
            
            <!-- Terra -->
            <div class="text-center ancient-parchment p-6 rounded-lg border-2 border-green-700 transform hover:scale-105 transition-all duration-300 hover:border-green-500">
                <div class="text-4xl mb-3 earth-animation">🌿</div>
                <h3 class="text-xl font-bold text-green-300 medieval-text">TERRA ANCESTRAL</h3>
                <p class="text-green-200 mt-2 text-sm">Inabalável e eterna</p>
                <p class="text-green-300 text-xs mt-1">Fraqueza: Fogo</p>
            </div>
            
            <!-- Ar -->
            <div class="text-center ancient-parchment p-6 rounded-lg border-2 border-gray-500 transform hover:scale-105 transition-all duration-300 hover:border-gray-300">
                <div class="text-4xl mb-3 wind-animation">💨</div>
                <h3 class="text-xl font-bold text-gray-300 medieval-text">VENTO DA MORTE</h3>
                <p class="text-gray-200 mt-2 text-sm">Invisível e mortal</p>
                <p class="text-gray-300 text-xs mt-1">Fraqueza: Nenhuma</p>
            </div>
        </div>

        <!-- Crônicas dos Guerreiros -->
        @if($games->count() > 0)
        <div class="ancient-parchment rounded-lg p-8 border-2 border-yellow-700 mb-12">
            <h2 class="text-3xl font-bold text-center blood-text medieval-text mb-8">
                📜 CRÔNICAS DOS SOBREVIVENTES 📜
            </h2>
            <div class="grid gap-4">
                @foreach($games as $game)
                <div class="dark-metal p-5 rounded border-2 
                    @if($game->is_completed) border-green-600 bg-green-900 bg-opacity-30
                    @elseif($game->player_health <= 0) border-red-600 bg-red-900 bg-opacity-30
                    @else border-yellow-600 @endif 
                    transform hover:scale-102 transition-all duration-300">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-4">
                            <div class="text-2xl">
                                @switch($game->player_element)
                                    @case('fogo') 🔥 @break
                                    @case('agua') 💧 @break
                                    @case('terra') 🌿 @break
                                    @case('ar') 💨 @break
                                @endswitch
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-yellow-200 medieval-text">{{ $game->player_name }}</h3>
                                <div class="flex space-x-3 text-sm mt-1">
                                    <span class="text-red-300">Fase {{ $game->current_phase }}/4</span>
                                    <span class="text-green-300">{{ $game->score }} pts</span>
                                    <span class="@if($game->player_health <= 0) text-red-400 @else text-yellow-300 @endif">
                                        {{ $game->player_health }}/100 HP
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            @if(!$game->is_completed && $game->player_health > 0)
                            <a href="{{ route('game.battle', $game->id) }}" 
                               class="medieval-text bg-gradient-to-r from-red-700 to-yellow-700 hover:from-red-800 hover:to-yellow-800 text-yellow-200 font-bold py-2 px-4 rounded border border-yellow-600 transition-all duration-300">
                                🗡️ Continuar
                            </a>
                            @elseif($game->is_completed)
                            <div class="medieval-text bg-gradient-to-r from-yellow-600 to-red-600 text-yellow-200 font-bold py-2 px-4 rounded border border-yellow-500 text-center">
                                👑 Lenda
                            </div>
                            <a href="{{ route('game.achievements', $game->id) }}" 
                               class="medieval-text bg-gradient-to-r from-purple-700 to-blue-700 hover:from-purple-800 hover:to-blue-800 text-white font-bold py-2 px-4 rounded border border-purple-500 transition-all duration-300">
                                🏆 Conquistas
                            </a>
                            @else
                            <div class="medieval-text bg-gradient-to-r from-gray-600 to-red-800 text-gray-300 font-bold py-2 px-4 rounded border border-red-700 text-center">
                                💀 Derrotado
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @else
        <!-- Mensagem Inicial -->
        <div class="text-center ancient-parchment rounded-lg p-12 border-2 border-red-700">
            <div class="text-5xl mb-4 blood-text">⚔️</div>
            <h2 class="text-3xl font-bold blood-text medieval-text mb-4">A JORNADA COMEÇA</h2>
            <p class="text-xl text-yellow-200 mb-6">Nenhum guerreiro deixou sua marca... ainda.</p>
            <p class="text-lg text-red-300 medieval-text">Seja o primeiro a enfrentar a escuridão!</p>
        </div>
        @endif

        <!-- Rodapé -->
        <div class="text-center mt-16 pt-8 border-t border-yellow-800">
            <p class="text-yellow-600 medieval-text">
                🏰 Batalha Elemental - Ana Clara, Estella e Glória
            </p>
        </div>
    </div>
</div>

<style>
.medieval-text {
    font-family: 'Times New Roman', serif;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
}

.blood-text {
    color: #ff4444;
    text-shadow: 0 0 10px #ff0000, 0 0 20px #ff0000, 0 0 30px #ff0000;
}

.ancient-parchment {
    background: linear-gradient(135deg, #8B4513 0%, #A0522D 50%, #8B4513 100%);
    background-color: #8B4513;
    border: 2px solid #5D4037;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
}

.dark-metal {
    background: linear-gradient(135deg, #2F4F4F 0%, #36454F 50%, #2F4F4F 100%);
    border: 1px solid #708090;
}

.pulse-dark {
    animation: pulse-dark 2s infinite;
}

@keyframes pulse-dark {
    0%, 100% { box-shadow: 0 0 20px rgba(255, 69, 0, 0.5); }
    50% { box-shadow: 0 0 30px rgba(255, 140, 0, 0.8); }
}

.flame-animation {
    animation: flame 1.5s ease-in-out infinite alternate;
}

@keyframes flame {
    from { transform: scale(1); opacity: 1; }
    to { transform: scale(1.1); opacity: 0.8; }
}

.water-animation {
    animation: water 2s ease-in-out infinite;
}

@keyframes water {
    0%, 100% { transform: translateY(0) scale(1); }
    50% { transform: translateY(-5px) scale(1.05); }
}

.earth-animation {
    animation: earth 3s ease-in-out infinite;
}

@keyframes earth {
    0%, 100% { transform: rotate(0deg); }
    50% { transform: rotate(5deg); }
}

.wind-animation {
    animation: wind 2s ease-in-out infinite;
}

@keyframes wind {
    0%, 100% { transform: translateX(0); }
    50% { transform: translateX(5px); }
}

.hover-scale-102:hover {
    transform: scale(1.02);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Efeito de fogo no título
    const title = document.querySelector('h1');
    setInterval(() => {
        title.style.textShadow = `0 0 ${10 + Math.random() * 10}px #ff0000`;
    }, 500);

    // Efeito de brilho nos elementos
    const elements = document.querySelectorAll('.ancient-parchment');
    elements.forEach(element => {
        element.addEventListener('mouseenter', function() {
            this.style.boxShadow = '0 0 25px rgba(255, 215, 0, 0.3)';
        });
        element.addEventListener('mouseleave', function() {
            this.style.boxShadow = '0 4px 15px rgba(0, 0, 0, 0.5)';
        });
    });
});
</script>
@endsection