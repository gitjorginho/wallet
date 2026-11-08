<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Carteira Digital') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-900 font-sans antialiased flex items-center justify-center min-h-screen">

    <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-sm border border-gray-200 text-center">
        <!-- Ícone simples de carteira -->
        <div class="mx-auto mb-4 w-12 h-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3" />
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-800 mb-2">
            Carteira Digital
        </h1>
        
        <p class="text-sm text-gray-600 mb-6">
            Gerencie seu saldo, depósitos, transferências e estornos de transações.
        </p>

        <div class="flex flex-col gap-3">
            @auth
                <a href="{{ route('dashboard') }}" class="w-full py-2 px-4 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition">
                    Ir para minha carteira
                </a>
            @else
                <a href="{{ route('login') }}" class="w-full py-2 px-4 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition">
                    Entrar
                </a>

                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="w-full py-2 px-4 bg-white text-gray-700 text-sm font-medium border border-gray-300 rounded-md hover:bg-gray-50 transition">
                        Criar conta
                    </a>
                @endif
            @endauth
        </div>
    </div>

</body>
</html>
