<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h2 class="text-2xl font-bold mb-4">Extrato de Movimentação</h2>
                        <ul class="divide-y divide-gray-200 max-h-80 overflow-y-auto">
                           
                          @if(count($transactions)==0)
                            <div class="text-center text-sm text-gray-500 py-6">Nenhuma transação encontrada.</div>
                          @endif
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

