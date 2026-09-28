<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SIMKES Puskesmas Sumbersari - Sistem Informasi Manajemen Kesehatan Lingkungan">
    <title>Login — SIMKES Puskesmas Sumbersari</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { inter: ['Inter', 'sans-serif'] },
                    colors: {
                        'medical-red':  '#E63946',
                        'medical-dark': '#C1121F',
                        'medical-light':'#FFE8EA',
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .auth-bg {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 50%, #f1f5f9 100%);
            min-height: 100vh;
        }
        .glass-card {
            background: rgba(255,255,255,0.97);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.8);
        }
        .medical-gradient {
            background: linear-gradient(135deg, #E63946 0%, #C1121F 100%);
        }
        .input-focus:focus {
            outline: none;
            border-color: #E63946;
            box-shadow: 0 0 0 3px rgba(230,57,70,0.15);
        }
        .btn-medical {
            background: linear-gradient(135deg, #E63946 0%, #C1121F 100%);
            transition: all 0.3s ease;
        }
        .btn-medical:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 25px rgba(230,57,70,0.4);
        }
        .btn-medical:active { transform: translateY(0); }
        .role-card {
            border: 2px solid #E5E7EB;
            transition: all 0.25s ease;
            cursor: pointer;
        }
        .role-card:hover { border-color: #E63946; background: #FFF5F5; transform: translateY(-2px); }
        .role-card.selected { border-color: #E63946; background: #FFF5F5; box-shadow: 0 0 0 3px rgba(230,57,70,0.15); }
        .floating-shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.06;
            animation: float 8s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        .fade-in { animation: fadeIn 0.6s ease forwards; }
        @keyframes fadeIn { from { opacity:0; transform: translateY(20px); } to { opacity:1; transform: translateY(0); } }
    </style>
</head>
<body class="auth-bg flex items-start justify-center p-4 py-8 relative overflow-x-hidden overflow-y-auto sm:items-center sm:py-4">

    {{-- Decorative floating shapes --}}
    <div class="floating-shape bg-red-500 w-96 h-96 -top-20 -left-20"></div>
    <div class="floating-shape bg-blue-500 w-64 h-64 bottom-10 right-10" style="animation-delay: 3s;"></div>
    <div class="floating-shape bg-red-300 w-48 h-48 top-1/2 left-1/3" style="animation-delay: 6s;"></div>

    <div class="w-full max-w-md relative z-10 fade-in">
        {{-- Logo Header --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 medical-gradient rounded-2xl shadow-2xl mb-4 rotate-3 hover:rotate-0 transition-transform duration-300">
                <i class="fas fa-clinic-medical text-white text-3xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">SIMKES</h1>
            <p class="text-gray-500 text-sm mt-1">Puskesmas Sumbersari</p>
            <p class="text-gray-400 text-xs mt-1">Sistem Informasi Manajemen Kesehatan Lingkungan</p>
        </div>

        {{-- Glass Card --}}
        <div class="glass-card rounded-3xl shadow-2xl p-8">
            @yield('content')
        </div>

        <p class="text-center text-gray-400 text-xs mt-6">
            &copy; {{ date('Y') }} Puskesmas Sumbersari — Dinas Kesehatan
        </p>
    </div>
</body>
</html>
