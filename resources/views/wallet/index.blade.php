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
                        <p class="text-lg mb-4">Saldo atual: R$ {{ number_format($wallet->balance, 2, ',', '.') }}</p>

                        <div class="flex gap-3 mb-4">
                            <form action="{{ route('transfer.form') }}">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                    Transferir
                                </button>
                            </form>

                            <form action="{{ route('deposit.form') }}">
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                    Depositar
                                </button>
                            </form>
                        </div>

                        @if(session('success'))
                            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-md">
                                {{ session('error') }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- COLUNA 2: Novo bloco (Extrato de Movimentação) -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h2 class="text-2xl font-bold mb-4">Extrato de Movimentação</h2>

                        <ul class="divide-y divide-gray-200 max-h-80 overflow-y-auto">
                            <!-- Exemplo de estrutura (substitua pelo seu ) -->
                           @foreach($transactions as $transaction) 
                            @if($transaction->type === 'transfer') 
                              @if($transaction->payer_wallet_id === $wallet->id)
                              <li class="py-3 flex justify-between items-center">
                                  <div>
                                      <p class="font-medium text-gray-800">Transferência enviada</p>
                                      <span class="text-xs text-gray-500">{{ $transaction->created_at->format('d/m/Y \à\s H:i') }}</span>
                                      <p class="text-xs text-gray-500">Para: {{ $transaction->payeeWallet->user->name }}</p>
                                  </div>
                                  <span class="font-semibold text-red-600">- R$ {{ number_format($transaction->amount, 2, ',', '.') }}</span>
                              </li>
                              @elseif($transaction->payee_wallet_id === $wallet->id)
                              <li class="py-3 flex justify-between items-center">
                                  <div>
                                      <p class="font-medium text-gray-800">Transferência recebida</p>
                                      <span class="text-xs text-gray-500">{{ $transaction->created_at->format('d/m/Y \à\s H:i') }}</span>
                                      <p class="text-xs text-gray-500">De: {{ $transaction->payerWallet->user->name }}</p>
                                  </div>
                                  <span class="font-semibold text-green-600">+ R$ {{ number_format($transaction->amount, 2, ',', '.') }}</span>
                              </li>
                              @endif
                            @endif
                            @if($transaction->type === 'deposit')
                            <li class="py-3 flex justify-between items-center">
                                <div>
                                    <p class="font-medium text-gray-800">Depósito recebido</p>
                                    <span class="text-xs text-gray-500">{{ $transaction->created_at->format('d/m/Y \à\s H:i') }}</span>
                                </div>
                                <span class="font-semibold text-green-600">+ R$ {{ number_format($transaction->amount, 2, ',', '.') }}</span>
                            </li>
                            @endif
                           @endforeach 
                          
                        </ul>
                    </div>
                </div>

            </div>
            <!-- FIM DO GRID -->

        </div>
    </div>
</x-app-layout>
