<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Batalha Elemental - Dark Fantasy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=MedievalSharp&family=Cinzel:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cinzel', serif;
            background: #0a0a0a url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" opacity="0.1"><rect fill="%231a0f0f" width="100" height="100"/><path fill="%23332222" d="M0 0h100v100H0z"/></svg>');
            color: #e0d3c1;
        }
        .medieval-text {
            font-family: 'MedievalSharp', cursive;
        }
        .blood-text {
            color: #8b0000;
            text-shadow: 2px 2px 4px #000;
        }
        .ancient-parchment {
            background: #2c1810 url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" opacity="0.1"><rect fill="%23332222" width="100" height="100"/><path fill="%234a2c2c" d="M0 0h100v100H0z"/></svg>');
            border: 2px solid #5d4037;
            box-shadow: 0 0 20px rgba(139, 0, 0, 0.3);
        }
        .dark-metal {
            background: linear-gradient(135deg, #2c2c2c 0%, #1a1a1a 100%);
            border: 1px solid #5d4037;
            box-shadow: inset 0 0 10px rgba(0,0,0,0.5);
        }
        @keyframes flame {
            0%, 100% { transform: scale(1) rotate(0deg); text-shadow: 0 0 10px #ff0000; }
            50% { transform: scale(1.1) rotate(2deg); text-shadow: 0 0 20px #ff6b00; }
        }
        @keyframes pulse-dark {
            0% { box-shadow: 0 0 10px rgba(139, 0, 0, 0.5); }
            50% { box-shadow: 0 0 20px rgba(139, 0, 0, 0.8); }
            100% { box-shadow: 0 0 10px rgba(139, 0, 0, 0.5); }
        }
        .flame-animation { animation: flame 3s infinite; }
        .pulse-dark { animation: pulse-dark 2s infinite; }
    </style>
</head>
<body class="bg-black">
    <!-- Background com textura medieval -->
    <div class="fixed inset-0 bg-cover bg-center opacity-20" style="background-image: url('data:image/svg+xml,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 100 100\"><defs><pattern id=\"grain\" width=\"100\" height=\"100\" patternUnits=\"userSpaceOnUse\"><circle cx=\"50\" cy=\"50\" r=\"1\" fill=\"%234a2c2c\" opacity=\"0.1\"/></pattern></defs><rect width=\"100\" height=\"100\" fill=\"url(%23grain)\"/></svg>')"></div>
    
    <div class="relative z-10">
        @yield('content')
    </div>

    <!-- Script do Confetti -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>
</body>
</html>