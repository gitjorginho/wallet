<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Wallet;
use App\Services\TransferService;
class WalletController extends Controller
{
  
  public function index()
  {
      $user = Auth::user();

      //garante q o usuario tenha uma carteira 
      if (!$user->wallet) {
        $wallet = $user->wallet()->create(['balance' => 0]);
      }else{
        $wallet = $user->wallet;
      }
      
      // carrega todas as movimentacoes da carteira do usuario
      $transactions = Transaction::where('payer_wallet_id', $wallet->id)
            ->orWhere('payee_wallet_id', $wallet->id)
            ->with(['payerWallet.user', 'payeeWallet.user']) 
            ->latest()
            ->get();

      //lista todos os usuarios para preencher o select de transferencia
      $otherWallets = Wallet::where('user_id', '!=', $user->id)
      ->with('user')
      ->get();

      return view('wallet.index', [
          'wallet' => $user->wallet,
          'transactions' => $transactions,
          'otherWallets' => $otherWallets,
      ]);
  }


  public function transferForm(){
    
    $user = Auth::user();
    $wallet = $user->wallet;

    //lista todos os usuarios para preencher o select de transferencia
    $otherWallets = Wallet::where('user_id', '!=', $user->id)
    ->with('user')
    ->get();

    return view('wallet.transfer',compact('wallet','otherWallets'));
  } 

  /**
   * Realiza a transferência de fundos entre carteiras.
   * 
   * @param  Request $request A instância da requisição HTTP.
   * @return \Illuminate\Http\RedirectResponse Redireciona de volta para o dashboard com uma mensagem de sucesso ou erro.
   * @throws \Exception Se ocorrer algum erro durante a transferência.
   */

  public function transfer(Request $request)
  {
    
    // 1. Validação estrita dos dados vindos da tela do Blade
    $validated = $request->validate([
        'payee_wallet_id' => 'required|exists:wallets,id',
        'amount' => 'required|numeric|min:0.01',
    ], [
        'payee_wallet_id.required' => 'Você precisa selecionar um destinatário.',
        'payee_wallet_id.exists' => 'O destinatário selecionado é inválido.',
        'amount.required' => 'Informe o valor da transferência.',
        'amount.numeric' => 'O valor deve ser um número válido.',
        'amount.min' => 'O valor mínimo para transferência é R$ 0,01.',
    ]);

     $payerWallet = Auth::user()->wallet; 
     try{
      
        $transferService = new TransferService();
        $transaction = $transferService->execute(
          $payerWallet->id, 
          $validated['payee_wallet_id'], 
          $validated['amount']);
        
        return redirect()->route('dashboard')->with('success', 'Transferência realizada com sucesso!');
     }catch(\Exception $e){
        return redirect()->route('dashboard')->with('error', 'Erro ao realizar a transferência: ' . $e->getMessage());
     }

  }

  public function depositForm()
  {
      $wallet = Auth::user()->wallet;
      return view('wallet.deposit',compact('wallet'));

  }

  /**
   * Realiza o depósito em uma carteira.
   */

  public function deposit(Request $request){
     
    // Validação estrita dos dados vindos da tela do Blade
    $validated = $request->validate([
        'amount' => 'required|numeric|min:0.01',
    ], [
        
        'amount.required' => 'Informe o valor do depósito.',
        'amount.numeric' => 'O valor deve ser um número válido.',
        'amount.min' => 'O valor mínimo para depósito é R$ 0,01.',
    ]);

    try{
      $transferService = new TransferService();
      $transaction = $transferService->deposit(
          Auth::user()->wallet->id, 
          $validated['amount']
      );

      return redirect()->route('deposit.form')->with('success', 'Depósito realizado com sucesso!');
    }catch(\Exception $e){
      return redirect()->route('deposit.form')->with('error', 'Erro ao realizar o depósito: ' . $e->getMessage());
    }


  }



}
