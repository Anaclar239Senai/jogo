@extends('layouts.app') 

@section('title', 'Conquistas de ' . $game->player_name)

@section('content')
<div class="container mx-auto p-4 max-w-2xl text-center">
    <h1 class="text-4xl font-extrabold mb-8 text-yellow-300 neon-title">
        🏆 CONQUISTAS ELEMENTAIS 🏆
    </h1>

    <div class="bg-gray-900 p-8 rounded-2xl shadow-2xl border-4 border-yellow-600 mb-8">
        <h2 class="text-3xl font-bold text-white mb-6 border-b border-gray-700 pb-3">
            Histórico de {{ $game->player_name }}
        </h2>
        
        <p class="text-gray-300 mb-6">
            Sua pontuação final: <span class="text-green-400 text-3xl font-mono">{{ $game->score }}</span> pontos!
        </p>

        <div class="space-y-4 text-left">
            <!-- Conquista Principal (Sempre Desbloqueada após a vitória) -->
            <div class="flex items-center bg-gray-800 p-4 rounded-xl border border-green-500 hover:scale-[1.02] transition duration-300">
                <span class="text-5xl mr-4">👑</span>
                <div>
                    <h3 class="font-bold text-green-400 text-xl">CAMPEÃO ABSOLUTO</h3>
                    <p class="text-sm text-gray-400">Dominou os 4 elementos e venceu a Batalha Final.</p>
                </div>
            </div>

            <!-- Exemplo de Conquista Baseada em Elemento -->
            <div class="flex items-center bg-gray-800 p-4 rounded-xl border border-red-500 hover:scale-[1.02] transition duration-300">
                <span class="text-5xl mr-4">💧</span>
                <div>
                    <h3 class="font-bold text-red-400 text-xl">MUITO FÁCIL</h3>
                    <p class="text-sm text-gray-400">Venceu a Batalha 2 (Mestre das Águas) com mais de 80 de vida.</p>
                </div>
            </div>

            <!-- Exemplo de Conquista Bloqueada -->
            <div class="flex items-center bg-gray-800 p-4 rounded-xl border border-gray-600 opacity-40">
                <span class="text-5xl mr-4">🔒</span>
                <div>
                    <h3 class="font-bold text-gray-500 text-xl">PERFEIÇÃO ELEMENTAL</h3>
                    <p class="text-sm text-gray-600">Vença o jogo inteiro sem usar Ataque Especial.</p>
                </div>
            </div>
        </div>
    </div>

    <a href="{{ route('game.index') }}" class="mt-8 inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold py-3 px-8 rounded-full transition duration-300 shadow-lg hover:shadow-xl">
        VOLTAR À TELA INICIAL
    </a>
</div>

<style>
.neon-title {
    text-shadow: 0 0 5px rgba(255, 255, 255, 0.8),
                 0 0 15px rgba(255, 255, 0, 0.7),
                 0 0 25px rgba(255, 165, 0, 0.6);
}
body {
    background-color: #121212; /* Fundo bem escuro */
}
</style>
@endsection