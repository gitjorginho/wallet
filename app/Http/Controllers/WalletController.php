<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Wallet;
use App\Services\TransferService;
use App\Http\Requests\Wallet\TransferRequest;
use App\Http\Requests\Wallet\DepositRequest;
use App\Http\Requests\Wallet\WithdrawRequest;

class WalletController extends Controller
{
  
  /**
   * Exibe o dashboard do usuário com informações da carteira e transações.
   */
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
     
      return view('wallet.index', [
          'wallet' => $user->wallet,
          'transactions' => $transactions
      ]);
  }

  /**
   * Exibe o formulário de transferência para o usuário.
   */
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
  public function transfer(TransferRequest $transferRequest)
  {
        
     $payerWallet = Auth::user()->wallet; 
     try{
        $transferService = new TransferService();
        $transaction = $transferService->transfer(
          $payerWallet->id, 
          $transferRequest['payee_wallet_id'], 
          $transferRequest['amount']);
        
        return redirect()->route('dashboard')->with('success', 'Transferência realizada com sucesso!');
     }catch(\Exception $e){
        return redirect()->route('transfer.form')->with('error', 'Erro ao realizar a transferência: ' . $e->getMessage());
     }

  }

  /**
   * Exibe o formulário de depósito para o usuário.
   */
  public function depositForm()
  {
      $wallet = Auth::user()->wallet;
      return view('wallet.deposit',compact('wallet'));
  }

  /**
   * Realiza o depósito em uma carteira.
   * @param  DepositRequest $depositRequest A instância da requisição HTTP contendo os dados do depósito.
   * @return \Illuminate\Http\RedirectResponse Redireciona de volta para o formulário
   * @throws \Exception Se ocorrer algum erro durante o depósito.
   */

  public function deposit(DepositRequest $depositRequest){
     
    
    try{
      $wallet = Auth::user()->wallet;
      $transferService = new TransferService();
      $transaction = $transferService->deposit(
          $wallet->id, 
          $depositRequest['amount']
      );

      return redirect()->route('dashboard')->with('success', 'Depósito realizado com sucesso!');
    }catch(\Exception $e){
      return redirect()->route('deposit.form')->with('error', 'Erro ao realizar o depósito: ' . $e->getMessage());
    }

  }

  /**
   * Exibe o formulário de saque para o usuário.
   */
  public function withdrawForm()
  {
      $wallet = Auth::user()->wallet;
      return view('wallet.withdraw',compact('wallet'));
  }

  /**
   * Realiza o saque de uma carteira.
   * @param  WithdrawRequest $withdrawRequest A instância da requisição HTTP contendo os dados do saque.
   * @return \Illuminate\Http\RedirectResponse Redireciona de volta para o formulário
   * @throws \Exception Se ocorrer algum erro durante o saque.
   */
  public function withdraw(WithdrawRequest $withdrawRequest)
  {
      try{
          $wallet = Auth::user()->wallet;
          $transferService = new TransferService();
          $transaction = $transferService->withdraw(
              $wallet->id,
              $withdrawRequest['amount']
          );

          return redirect()->route('dashboard')->with('success', 'Saque realizado com sucesso!');
      }catch(\Exception $e){
          return redirect()->route('withdraw.form')->with('error', 'Erro ao realizar o saque: ' . $e->getMessage());
      }
  }

  public function reverseTransaction(Transaction $transaction)
  {
      try {
          $transferService = new TransferService();
          $transferService->reverseTransaction($transaction);

          return redirect()->route('dashboard')->with('success', 'Transação estornada com sucesso!');
      } catch (\Exception $e) {
          return redirect()->route('dashboard')->with('error', 'Erro ao estornar a transação: ' . $e->getMessage());
      }
  }

}
