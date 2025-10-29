@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto text-center bg-gray-800 rounded-lg shadow-lg p-8">
    <h1 class="text-4xl font-bold text-yellow-400 mb-6">🎊 Parabéns! 🎊</h1>
    
    <div class="bg-gray-700 rounded-lg p-6 mb-6">
        <h2 class="text-2xl font-bold mb-4">{{ $game->player_name }}</h2>
        <p class="text-xl mb-2">Elemento: <span class="capitalize">{{ $game->player_element }}</span></p>
        <p class="text-xl mb-2">Pontuação Final: <span class="text-yellow-400 text-2xl">{{ $game->score }}</span></p>
        <p class="text-lg">Você dominou todos os elementos e se tornou o Mestre Elemental!</p>
    </div>

    <div class="space-y-4">
        
        <!-- NOVO BOTÃO: Chama a rota de Conquistas -->
        <a href="{{ route('game.achievements', $game->id) }}" 
           class="block bg-yellow-600 hover:bg-yellow-700 text-gray-900 font-bold py-3 px-6 rounded transition duration-200">
            🏆 VER CONQUISTAS 🌟
        </a>
        
        <!-- Botões existentes -->
        <a href="{{ route('game.create') }}" 
           class="block bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded">
            🎮 Novo Jogo
        </a>
        <a href="{{ route('game.index') }}" 
           class="block bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded">
            📊 Ver Todos os Jogos
        </a>
    </div>
</div>
@endsection