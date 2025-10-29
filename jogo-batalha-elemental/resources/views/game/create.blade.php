@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center">
    <div class="ancient-parchment rounded-2xl p-8 border-4 border-yellow-700 max-w-2xl w-full mx-4">
        <h1 class="text-4xl font-bold text-center blood-text medieval-text mb-2">
            🗡️ FORJA SEU DESTINO 🛡️
        </h1>
        <p class="text-center text-yellow-200 mb-8 medieval-text">Escolha sabiamente, guerreiro...</p>
        
        <form action="{{ route('game.store') }}" method="POST">
            @csrf
            
            <!-- Nome do Guerreiro -->
            <div class="mb-8">
                <label class="block text-lg blood-text medieval-text mb-3">NOME DO GUERREIRO</label>
                <input type="text" name="player_name" required 
                       class="w-full px-4 py-3 dark-metal rounded border-2 border-yellow-700 text-yellow-200 medieval-text focus:border-red-600 focus:outline-none"
                       placeholder="Seu nome será lembrado...">
            </div>

            <!-- Escolha do Elemento -->
            <div class="mb-8">
                <label class="block text-lg blood-text medieval-text mb-4">SELECIONE SEU PODER</label>
                <div class="grid grid-cols-2 gap-4">
                    <!-- Fogo -->
                    <label class="flex items-center p-4 dark-metal rounded border-2 border-red-700 cursor-pointer hover:border-red-500 transition-all duration-300">
                        <input type="radio" name="player_element" value="fogo" required class="mr-3">
                        <div class="flex-1">
                            <div class="text-2xl flame-animation">🔥</div>
                            <span class="block blood-text medieval-text mt-1">FOGO</span>
                            <span class="text-xs text-red-300">Destruição absoluta</span>
                        </div>
                    </label>
                    
                    <!-- Água -->
                    <label class="flex items-center p-4 dark-metal rounded border-2 border-blue-700 cursor-pointer hover:border-blue-500 transition-all duration-300">
                        <input type="radio" name="player_element" value="agua" required class="mr-3">
                        <div class="flex-1">
                            <div class="text-2xl">💧</div>
                            <span class="block text-blue-300 medieval-text mt-1">ÁGUA</span>
                            <span class="text-xs text-blue-300">Adaptabilidade mortal</span>
                        </div>
                    </label>
                    
                    <!-- Terra -->
                    <label class="flex items-center p-4 dark-metal rounded border-2 border-green-700 cursor-pointer hover:border-green-500 transition-all duration-300">
                        <input type="radio" name="player_element" value="terra" required class="mr-3">
                        <div class="flex-1">
                            <div class="text-2xl">🌿</div>
                            <span class="block text-green-300 medieval-text mt-1">TERRA</span>
                            <span class="text-xs text-green-300">Resistência infinita</span>
                        </div>
                    </label>
                    
                    <!-- Ar -->
                    <label class="flex items-center p-4 dark-metal rounded border-2 border-gray-500 cursor-pointer hover:border-gray-400 transition-all duration-300">
                        <input type="radio" name="player_element" value="ar" required class="mr-3">
                        <div class="flex-1">
                            <div class="text-2xl">💨</div>
                            <span class="block text-gray-300 medieval-text mt-1">AR</span>
                            <span class="text-xs text-gray-300">Liberdade implacável</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Botão de Início -->
            <button type="submit" 
                    class="w-full medieval-text bg-gradient-to-r from-red-800 to-yellow-800 hover:from-red-900 hover:to-yellow-900 text-yellow-200 font-bold text-xl py-4 px-8 rounded-lg transition-all duration-300 transform hover:scale-105 border-2 border-yellow-600 pulse-dark">
                🗡️ INICIAR BATALHA 🛡️
            </button>
        </form>
    </div>
</div>
@endsection