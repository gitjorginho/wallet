<?php
namespace App\Services;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class TransactionService{
  /**
   * Realiza operação de tranferência  
   * @param int $payerWalletId id da carteira pagadora
   * @param int $payeeWalletId id da carteira recebedora
   * @param float $amount valor a ser transferido
   */

  public function transfer (int $payerWalletId, int $payeeWalletId, float $amount): Transaction
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


    Log::channel('transactions')->info('Transferência financeira concluída', [
      'transaction_id' => $transaction->id,
      'payer_wallet_id' => $payerWallet->id,
      'payee_wallet_id' => $payeeWallet->id,
      'amount' => $amount,
    ]);

    return  $transaction;
    });
  }

  /**
   * Realiza o Deposito em uma carteira específica.
   * 
   * @param  int $payeeWalletId O ID da carteira que receberá o depósito.
   * @param  float $amount O valor a ser depositado.
   * @return Transaction A transação de depósito criada.
   * @throws Exception Se o valor do depósito for inválido.
   */
  public function deposit(int $walletId, float $amount): Transaction
  {
    //Não permite deposito com valor 0 ou menor
    if($amount <= 0){
      throw new Exception("O valor do depósito deve ser maior que zero.");
    }

    return DB::transaction(function () use ($walletId, $amount) {
        
    //garante que nenhuma atualizacao seja feita na carteira durante a transacao evitatando 
        //problemas de concorrencias e inconsistencia do saldo
        $wallet = Wallet::where('id', $walletId)->lockForUpdate()->firstOrFail();

        //adiciona o valor na carteira
        $wallet->balance = bcadd($wallet->balance, $amount, 2);
        $wallet->save();

        //registra a trasacao de deposito com sucesso
        $transaction =  Transaction::create([
            'payer_wallet_id' => null, // Nenhum pagador para depósito
            'payee_wallet_id' => $walletId,
            'amount' => $amount,
             'type' => 'deposit',
            'status' => 'completed',
        ]);

        Log::channel('transactions')->info('Depósito realizado com sucesso', [
         'transaction_id' => $transaction->id,
         'payee_wallet_id' => $wallet->id,
         'amount' => $amount,
         'new_balance' => $wallet->balance,
        ]);
        
        return $transaction;
    });
  }

  /**
   * Realiza o Saque de uma carteira específica.
   * 
   * @param  int $payerWalletId O ID da carteira que realizará o saque.
   * @param  float $amount O valor a ser sacado.
   * @return Transaction A transação de saque criada.
   * @throws Exception Se o valor do saque for inválido ou se o saldo for insuficiente.
   */
  public function withdraw(int $walletId, float $amount): Transaction
  {
    //Não permite saque com valor 0 ou menor
    if($amount <= 0){
      throw new Exception("O valor do saque deve ser maior que zero."); 
    }

    return DB::transaction(function () use ($walletId, $amount) {
        
        //garante que nenhuma atualizacao seja feita na carteira durante a transacao evitatando 
        //problemas de concorrencias e inconsistencia do saldo
        $wallet = Wallet::where('id', $walletId)->lockForUpdate()->firstOrFail();

        // verifica se o saldo da carteira é suficiente 
        if ($wallet->balance < $amount) {
            throw new Exception("Saldo insuficiente para realizar o saque.");
        }    

        //deduz o valor da carteira
        $wallet->balance = bcsub($wallet->balance, $amount, 2);
        $wallet->save();

        //registra a trasacao de saque com sucesso
        $transaction = Transaction::create([
            'payer_wallet_id' => $wallet->id,
            'payee_wallet_id' => null, // Nenhum recebedor para saque
            'amount' => $amount,
            'type' => 'withdraw',
            'status' => 'completed',
        ]);
        
        //log da operacao
        Log::channel('transactions')->info('Saque realizado com sucesso', [
          'transaction_id' => $transaction->id,
          'wallet_id' => $wallet->id,
          'amount' => $transaction->amount,
          'new_balance' => $wallet->balance
        ]);

        return $transaction;  
    });


  }  

  /**
   * Methodo responsável por fazer o estono da trasação finaceira
   * @param Transaction
   * @return void
   * * @throws Exception caso o estorno já tenha sido realizado
   */
  public function reverseTransaction(Transaction $transaction): void
  {
      DB::transaction(function () use ($transaction) {
          // Verifica se a transação já foi estornada
          if ($transaction->status === 'reversed') {
              throw new Exception("Esta transação já foi estornada.");
          }
          // Reverte a transação dependendo do tipo
          switch ($transaction->type) {
              case 'transfer':
                  $payerWallet = Wallet::where('id', $transaction->payer_wallet_id)->lockForUpdate()->firstOrFail();
                  $payeeWallet = Wallet::where('id', $transaction->payee_wallet_id)->lockForUpdate()->firstOrFail();

                  // Reverte a transferência
                  $payeeWallet->balance = bcsub($payeeWallet->balance, $transaction->amount, 2);
                  $payeeWallet->save();
                  $payerWallet->balance = bcadd($payerWallet->balance, $transaction->amount, 2);
                  $payerWallet->save();
                  break;

              case 'deposit':
                  $wallet = Wallet::where('id', $transaction->payee_wallet_id)->lockForUpdate()->firstOrFail();

                  // Reverte o depósito
                  $wallet->balance = bcsub($wallet->balance, $transaction->amount, 2);
                  $wallet->save();
                  break;

              case 'withdraw':
                  $wallet = Wallet::where('id', $transaction->payer_wallet_id)->lockForUpdate()->firstOrFail();

                  // Reverte o saque
                  $wallet->balance = bcadd($wallet->balance, $transaction->amount, 2);
                  $wallet->save();
                  break;

              default:
                  throw new Exception("Tipo de transação desconhecido.");
          }

          // Marca a transação como estornada
          $transaction->status = 'reversed';
          $transaction->save();

          //log da operacao
        Log::channel('transactions')->warning('Transação financeira estornada', [
          'transaction_id' => $transaction->id,
          'reversed_by_wallet_id' => $wallet->id,
          'amount' => $transaction->amount,
        ]);
      });
  }
}