<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-bold mb-4">Saldo da Carteira</h1>
                    <p class="text-lg mb-4">Saldo atual: R$ {{ number_format($wallet->balance, 2, ',', '.') }}</p>

                    <h2 class="text-xl font-semibold mb-2">Transferir Fundos</h2>
                    <form action="{{ route('transfer') }}" method="POST" class="mb-4">
                        @csrf
                        <div class="mb-2">
                          
                            <label for="payee_wallet_id" class="block text-sm font-medium text-gray-700">ID da Carteira do Destinatário:</label>
                            <select name="payee_wallet_id" id="payee_wallet_id" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                
                              @foreach($otherWallets as $recipientWallet)
                                       <option value="{{ $recipientWallet->id }}">ID: {{ $recipientWallet->id }} - {{ $recipientWallet->user->name }}</option>
                                @endforeach
                            </select>
                            @error('payee_wallet_id')
                              <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-2">
                            <label for="amount" class="block text-sm font-medium text-gray-700">Valor a Transferir:</label>
                            <input type="number" name="amount" id="amount" step="0.01" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            @error('amount')
                              <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        @error('transfer_error')
                          <div class="mt-4 p-3 bg-red-100 text-red-700 rounded-lg text-sm">
                              {{ $message }}
                          </div>
                        @enderror
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Transferir</button>
                      
                    </form>
                    <form action="{{ route('deposit.form') }}">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Depositar</button>
                    </form>

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
        </div>
      
    </div>
</x-app-layout>