@extends('layouts.app') 

@section('title', 'Conquistas de ' . $game->player_name)

@section('content')
<div class="container mx-auto p-4 max-w-4xl text-center">
    <h1 class="text-4xl font-extrabold mb-8 text-yellow-300 neon-title">
        🏆 CONQUISTAS ELEMENTAIS 🏆
    </h1>

    <div class="bg-gray-900 p-8 rounded-2xl shadow-2xl border-4 border-yellow-600 mb-8">
        <h2 class="text-3xl font-bold text-white mb-6 border-b border-gray-700 pb-3">
            Histórico de {{ $game->player_name }}
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-gray-800 p-4 rounded-xl border border-blue-500">
                <p class="text-gray-300 text-sm">Pontuação</p>
                <p class="text-green-400 text-2xl font-mono font-bold">{{ $game->score }}</p>
            </div>
            <div class="bg-gray-800 p-4 rounded-xl border border-green-500">
                <p class="text-gray-300 text-sm">Fase Alcançada</p>
                <p class="text-yellow-400 text-2xl font-bold">{{ $game->current_phase }}/4</p>
            </div>
            <div class="bg-gray-800 p-4 rounded-xl border border-purple-500">
                <p class="text-gray-300 text-sm">Conquistas</p>
                <p class="text-blue-400 text-2xl font-bold">
                    {{ $game->unlockedAchievements->count() }}/{{ $game->achievements->count() }}
                </p>
            </div>
        </div>

        <!-- Barra de Progresso -->
        @php
            $unlockedCount = $game->unlockedAchievements->count();
            $totalCount = $game->achievements->count();
            $percentage = $totalCount > 0 ? round(($unlockedCount / $totalCount) * 100) : 0;
        @endphp
        
        <div class="bg-gray-800 p-4 rounded-xl mb-6">
            <div class="flex justify-between items-center mb-2">
                <span class="text-gray-300">Progresso Geral</span>
                <span class="font-bold text-yellow-400">{{ $percentage }}%</span>
            </div>
            <div class="w-full bg-gray-700 rounded-full h-4">
                <div class="bg-gradient-to-r from-green-400 to-blue-500 h-4 rounded-full transition-all duration-1000" 
                     style="width: {{ $percentage }}%"></div>
            </div>
        </div>

        <!-- Lista de Conquistas Reais -->
        <div class="space-y-4 text-left">
            @forelse($game->achievements as $achievement)
                <div class="flex items-center bg-gray-800 p-4 rounded-xl border 
                    {{ $achievement->unlocked_at ? 'border-green-500 hover:scale-[1.02]' : 'border-gray-600 opacity-60' }} 
                    transition duration-300">
                    
                    <span class="text-4xl mr-4 {{ $achievement->unlocked_at ? 'text-yellow-400' : 'text-gray-500' }}">
                        {!! $achievement->icon ?? '⭐' !!}
                    </span>
                    
                    <div class="flex-1">
                        <h3 class="font-bold {{ $achievement->unlocked_at ? 'text-green-400 text-xl' : 'text-gray-500 text-xl' }}">
                            {{ $achievement->name }}
                            @if($achievement->unlocked_at)
                                <span class="text-sm text-green-300 ml-2">✅</span>
                            @else
                                <span class="text-sm text-gray-500 ml-2">🔒</span>
                            @endif
                        </h3>
                        <p class="text-sm {{ $achievement->unlocked_at ? 'text-gray-300' : 'text-gray-600' }} mt-1">
                            {{ $achievement->description }}
                        </p>
                        @if($achievement->unlocked_at)
                            <p class="text-xs text-green-400 mt-2">
                                Desbloqueada em {{ $achievement->unlocked_at->format('d/m/Y H:i') }}
                            </p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <span class="text-6xl mb-4">😢</span>
                    <h3 class="text-xl font-bold text-gray-400">Nenhuma conquista encontrada</h3>
                    <p class="text-gray-500 mt-2">As conquistas serão criadas quando você iniciar um novo jogo.</p>
                </div>
            @endforelse
        </div>

        <!-- Mensagem Especial se completou todas -->
        @if($unlockedCount === $totalCount && $totalCount > 0)
            <div class="mt-6 bg-gradient-to-r from-green-500 to-blue-500 rounded-xl p-6 text-center">
                <div class="text-4xl mb-4">🎉</div>
                <h3 class="text-2xl font-bold text-white mb-2">PARABÉNS, MESTRE ELEMENTAL!</h3>
                <p class="text-white text-lg">Você desbloqueou todas as conquistas disponíveis!</p>
                <p class="text-white/80 mt-2">Seu domínio sobre os elementos é absoluto! 🌟</p>
            </div>
        @endif

        <!-- Dicas para Conquistas Restantes -->
        @if($unlockedCount < $totalCount && $totalCount > 0)
            <div class="mt-6 bg-yellow-500/10 border border-yellow-500 rounded-xl p-4">
                <h3 class="text-lg font-bold text-yellow-300 mb-2">💡 Conquistas Restantes:</h3>
                <div class="text-sm text-gray-300 text-left space-y-1">
                    @foreach($game->lockedAchievements as $achievement)
                        <p>• <strong>{{ $achievement->name }}</strong>: {{ $achievement->description }}</p>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <a href="{{ route('game.battle', $game->id) }}" 
           class="bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold py-3 px-8 rounded-full transition duration-300 shadow-lg hover:shadow-xl">
            VOLTAR À BATALHA
        </a>
        <a href="{{ route('game.index') }}" 
           class="bg-gray-600 hover:bg-gray-700 text-white font-extrabold py-3 px-8 rounded-full transition duration-300 shadow-lg hover:shadow-xl">
            NOVO JOGO
        </a>
    </div>
</div>

<style>
.neon-title {
    text-shadow: 0 0 5px rgba(255, 255, 255, 0.8),
                 0 0 15px rgba(255, 255, 0, 0.7),
                 0 0 25px rgba(255, 165, 0, 0.6);
}
body {
    background-color: #121212;
}
</style>
@endsection