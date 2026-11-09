<?php
namespace App\Services;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class TransferService{
  /**
   * Realiza operação de tranferência  
   * @param int $payerWalletId id da carteira pagadora
   * @param int $payeeWalletId id da carteira recebedora
   * @param float $amount valor a ser transferido
   */

  public function execute (int $payerWalletId, int $payeeWalletId, float $amount): Transaction
  {
    //Não permite transferencia com valor 0 ou menor
    if($amount <= 0){
      throw new Exception("O valor da tranferência deve ser maior que zero.");
    }

    //Não permite realizar tranferencia para a mesma carteira
    if($payerWalletId == $payeeWalletId){
      throw new Exception("Não é possível fazer traferência para a mesma carteira");
    }

    return DB::transaction(function () use ($payerWalletId,$payeeWalletId,$amount){

    //garante que nenhuma atualizacao seja feita na carteira durante a transacao evitatando 
    //problemas de concorrencias e inconsistencia do saldo
    $payerWallet = Wallet::where('id', $payerWalletId)->lockForUpdate()->firstOrFail();
    $payeeWallet = Wallet::where('id', $payeeWalletId)->lockForUpdate()->firstOrFail();

    // verifica se o saldo do pagador é suficiente 
    if ($payerWallet->balance < $amount) {
        throw new Exception("Saldo insuficiente para realizar a transferência.");
    }    

     //deduz o valor da carteira pagadora
     $payerWallet->balance = bcsub($payerWallet->balance, $amount, 2);
     $payerWallet->save();

    //adiciona o valor na carteira recebedora
    $payeeWallet->balance = bcadd($payeeWallet->balance, $amount, 2);
    $payeeWallet->save();

     //registra a trasacao com sucesso
     $transaction =  Transaction::create([
        'payer_wallet_id' => $payerWalletId,
        'payee_wallet_id' => $payeeWalletId,
        'amount' => $amount,
        'type' => 'transfer',
        'status' => 'completed',
     ]);  

    //resgistra o log da operação
    Log::channel('transactions')->info('Transferência financeira concluída', [
      'transaction_id' => $transaction->id,
      'payer_wallet_id' => $payerWallet->id,
      'payee_wallet_id' => $payeeWallet->id,
      'amount' => $amount,
      'payer_new_balance' => $payerWallet->balance, 
      'payee_new_balance' => $payeeWallet->balance, 
    ]);

    return  $transaction;
    });
  }

  
  /**
   * Methodo responsável por fazer o estono da trasação finaceira
   * @param Transaction
   * @return void
   * @throws Exception caso o estorno já tenha sido realizado
   */
  public function reverse(Transaction $transaction){
    

      // Verifica se a transação já foi estornada
      if ($transaction->status === 'reversed') {
        throw new Exception("Esta transação já foi estornada.");
      }
    DB::transaction(function () use ($transaction) {
     
      $payerWallet = Wallet::where('id', $transaction->payer_wallet_id)->lockForUpdate()->firstOrFail();
      $payeeWallet = Wallet::where('id', $transaction->payee_wallet_id)->lockForUpdate()->firstOrFail();

      // Reverte a transferência
      $payeeWallet->balance = bcsub($payeeWallet->balance, $transaction->amount, 2);
      $payeeWallet->save();
      $payerWallet->balance = bcadd($payerWallet->balance, $transaction->amount, 2);
      $payerWallet->save();
   
      // Marca a transação como estornada
      $transaction->status = 'reversed';
      $transaction->save();

      //log da operacao
      Log::channel('transactions')->warning('Transação de transferência estornada', [
          'transaction_id' => $transaction->id,
          'payer_wallet_id' => $payerWallet->id,
          'payee_wallet_id' => $payeeWallet->id,
          'amount' => $transaction->amount,
          'payer_new_balance' => $payerWallet->balance, 
          'payee_new_balance' => $payeeWallet->balance, 
        ]);

    });

  }


  
  
}