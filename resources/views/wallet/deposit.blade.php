
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Depósito') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-bold mb-4">Saldo da Carteira</h1>
                    <p class="text-lg mb-4">Saldo atual: <b>R$ {{ number_format($wallet->balance, 2, ',', '.') }}</b></p>

                    <h2 class="text-xl font-semibold mb-2">Depositar Fundos</h2>
                    <form action="{{ route('deposit') }}" method="POST" class="mb-4">
                        @csrf
                        <div class="mb-2">
                            <label for="amount" class="block text-sm font-medium text-gray-700">Valor a Depositar:</label>
                                                     
                            <input type="text" 
                                inputmode="numeric" 
                                name="amount" 
                                id="amount" 
                                placeholder="0,00" 
                                required 
                                class="currency-mask mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            >
                            @error('amount')
                              <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        @error('deposit_error')
                          <div class="mt-4 p-3 bg-red-100 text-red-700 rounded-lg text-sm">
                              {{ $message }}
                          </div>
                        @enderror
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