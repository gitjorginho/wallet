<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- GRID DE 2 COLUNAS -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- COLUNA 1: Seu bloco atual (Saldo e Ações) -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h1 class="text-2xl font-bold mb-4">Saldo da Carteira</h1>
                        <p class="text-lg mb-4">Saldo atual: <b>R$ {{ number_format($wallet->balance, 2, ',', '.') }}</b></p>

                        <div class="flex gap-3 mb-4">
                            <form action="{{ route('transfer.form') }}">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
    </svg>
    <span>Transferir</span>
                                </button>
                            </form>

                            <form action="{{ route('deposit.form') }}">
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    <span>Depositar</span>
                                </button>
                            </form>
                            <form action="{{ route('withdraw.form') }}">
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25" />
    </svg>
                                    <span>Sacar</span>
                                </button>
                            </form>
                        </div>

                       
                    </div>
                </div>

                <!-- COLUNA 2: Novo bloco (Extrato de Movimentação) -->
                @include('wallet.components.extract_movement', ['transactions' => $transactions, 'wallet' => $wallet])
                            </div>
            <!-- FIM DO GRID -->
            <div style="margin-top: 20px;">
                
            @if(session('success'))
                           <div x-data="{ show: true }" 
         x-init="setTimeout(() => show = false, 4000)" 
         x-show="show" 
         x-transition.duration.500ms
         class="mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-md shadow-sm">
        {{ session('success') }}
    </div>
                        @endif

                        @if(session('error'))
                           <div x-data="{ show: true }" 
         x-init="setTimeout(() => show = false, 5000)" 
         x-show="show" 
         x-transition.duration.500ms
         class="mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md shadow-sm">
        {{ session('error') }}
    </div>
                        @endif
                              </div>
        </div>
    </div>
</x-app-layout>
