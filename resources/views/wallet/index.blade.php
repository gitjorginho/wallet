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
                                      <p class="font-medium text-gray-800">Transferência enviada 
                                        @if($transaction->status == 'reversed')
                                        <spam style="color: red;">(Estonado)</spam>  
                                        @endif
                                      </p>
                                      <span class="text-xs text-gray-500">{{ $transaction->created_at->format('d/m/Y \à\s H:i') }}</span>
                                      <p class="text-xs text-gray-500">Para: {{ $transaction->payeeWallet->user->name }}</p>
                                      @if($transaction->status !== 'reversed')
                                      <form action="{{ route('transactions.reverse', $transaction->id) }}" method="POST" >
                                        @csrf
                                        <button type="submit" 
        onclick="return confirm('Tem certeza que deseja estornar esta transação?')" 
        class="inline-flex flex-row items-center gap-1.5 px-2.5 py-1 text-xs font-medium bg-red-600 text-white rounded hover:bg-red-700 transition whitespace-nowrap">
    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
    </svg>
    <span>Estornar</span>
</button>
                                      </form>
                                     @endif
                                      
                                  </div>
                                  <span class="font-semibold text-red-600">- R$ {{ number_format($transaction->amount, 2, ',', '.') }}</span>
                              </li>
                              @elseif($transaction->payee_wallet_id === $wallet->id)
                              <li class="py-3 flex justify-between items-center">
                                  <div>
                                      <p class="font-medium text-gray-800">Transferência recebida
                                        @if($transaction->status == 'reversed')
                                        <spam style="color: red;">(Estonado)</spam>  
                                        @endif
                                      </p>
                                      <span class="text-xs text-gray-500">{{ $transaction->created_at->format('d/m/Y \à\s H:i') }}</span>
                                      <p class="text-xs text-gray-500">De: {{ $transaction->payerWallet->user->name }}</p>
                                      @if($transaction->status !== 'reversed')
                                      <form action="{{ route('transactions.reverse', $transaction->id) }}" method="POST" >
                                        @csrf
                                        <button type="submit" 
        onclick="return confirm('Tem certeza que deseja estornar esta transação?')" 
        class="inline-flex flex-row items-center gap-1.5 px-2.5 py-1 text-xs font-medium bg-red-600 text-white rounded hover:bg-red-700 transition whitespace-nowrap">
    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
    </svg>
    <span>Estornar</span>
</button>
                                      </form>
                                     @endif
                                  </div>
                                  <span class="font-semibold text-green-600">+ R$ {{ number_format($transaction->amount, 2, ',', '.') }}</span>
                              </li>
                              @endif
                            @endif
                            @if($transaction->type === 'deposit')
                            <li class="py-3 flex justify-between items-center">
                                <div>
                                    <p class="font-medium text-gray-800">Depósito recebido
                                      @if($transaction->status == 'reversed')
                                        <spam style="color: red;">(Estonado)</spam>  
                                        @endif
                                    </p>
                                    <span class="text-xs text-gray-500">{{ $transaction->created_at->format('d/m/Y \à\s H:i') }}</span>
                                    @if($transaction->status !== 'reversed')
                                      <form action="{{ route('transactions.reverse', $transaction->id) }}" method="POST" >
                                        @csrf
                                        <button type="submit" 
        onclick="return confirm('Tem certeza que deseja estornar esta transação?')" 
        class="inline-flex flex-row items-center gap-1.5 px-2.5 py-1 text-xs font-medium bg-red-600 text-white rounded hover:bg-red-700 transition whitespace-nowrap">
    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
    </svg>
    <span>Estornar</span>
</button>
                                      </form>
                                     @endif
                                </div>
                                <span class="font-semibold text-green-600">+ R$ {{ number_format($transaction->amount, 2, ',', '.') }}</span>
                            </li>
                            @endif
                            @if($transaction->type === 'withdraw')
                            <li class="py-3 flex justify-between items-center">
                                <div>
                                    <p class="font-medium text-gray-800">Retirada
                                        @if($transaction->status == 'reversed')
                                          <spam style="color: red;">(Estonado)</spam>  
                                        @endif
                                    </p>
                                    <span class="text-xs text-gray-500">{{ $transaction->created_at->format('d/m/Y \à\s H:i') }}</span>
                                    @if($transaction->status !== 'reversed')
                                      <form action="{{ route('transactions.reverse', $transaction->id) }}" method="POST" >
                                        @csrf
                                        <button type="submit" 
        onclick="return confirm('Tem certeza que deseja estornar esta transação?')" 
        class="inline-flex flex-row items-center gap-1.5 px-2.5 py-1 text-xs font-medium bg-red-600 text-white rounded hover:bg-red-700 transition whitespace-nowrap">
    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
    </svg>
    <span>Estornar</span>
</button>
                                      </form>
                                     @endif
                                </div>
                                <span class="font-semibold text-red-600">- R$ {{ number_format($transaction->amount, 2, ',', '.') }}</span>
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
