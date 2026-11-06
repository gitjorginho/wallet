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
                    
                    <form action="{{ route('transfer.form') }}" class="mb-4">
                        @csrf
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
