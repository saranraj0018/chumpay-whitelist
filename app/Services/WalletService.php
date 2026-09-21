<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\WalletOffer;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class WalletService
{
    public function saveWallet(int $userId, float $amount, string $transactionId, string $orderId): array
    {
        return DB::transaction(function () use ($userId, $amount, $transactionId, $orderId) {

            $offer       = $this->findMatchingOffer($amount);
            $bonusData   = $this->calculateBonus($offer);
            $wallet      = $this->updateWallet($userId, $amount, $bonusData);
            $transaction = $this->createTransaction($userId, $amount, $bonusData, $transactionId, $orderId);

            return [
                'wallet'      => $wallet,
                'transaction' => $transaction,
                'bonus'       => $bonusData,
            ];
        });
    }

    private function findMatchingOffer(float $amount): ?WalletOffer
    {
        return WalletOffer::where('min_amount', '<=', $amount)
            ->where('max_amount', '>=', $amount)
            ->where('is_active', 1)
            ->first();
    }

    private function calculateBonus(?WalletOffer $offer): array
    {
        if (!$offer) {
            return [
                'offer_id'          => null,
                'bonus_amount'      => 0.00,
                'bonus_valid_until' => null,
            ];
        }

        return [
            'offer_id'          => $offer->id,
            'bonus_amount'      => $offer->bonus_amount,
            'bonus_valid_until' => now()->addDays($offer->valid_days)->toDateString(),
        ];
    }

    private function updateWallet(int $userId, float $amount, array $bonusData): Wallet
    {
        $wallet = Wallet::where('user_id', $userId)->first();
        if (!$wallet) {
            $wallet                = new Wallet();
            $wallet->user_id       = $userId;
            $wallet->balance       = 0.00;
            $wallet->bonus_balance = 0.00;
        }
        // Credit balance
        $wallet->balance       = $wallet->balance + $amount;
        $wallet->bonus_balance = $wallet->bonus_balance + $bonusData['bonus_amount'];
        // Apply offer if matched
        if ($bonusData['offer_id']) {
            $wallet->wallet_offer_id   = $bonusData['offer_id'];
            $wallet->bonus_valid_until = $bonusData['bonus_valid_until'];
        }
        $wallet->save();
        return $wallet;
    }

    private function createTransaction(int $userId, float $amount, array $bonusData, string $transactionId, string $orderId): WalletTransaction
    {
        $transaction                    = new WalletTransaction();
        $transaction->user_id           = $userId;
        $transaction->wallet_offer_id   = $bonusData['offer_id'];
        $transaction->transaction_id    = $transactionId;
        $transaction->order_id          = $orderId;
        $transaction->type              = 'credit';
        $transaction->amount            = $amount;
        $transaction->bonus_amount      = $bonusData['bonus_amount'];
        $transaction->bonus_valid_until = $bonusData['bonus_valid_until'];
        $transaction->description       = 'Wallet top-up';
        $transaction->save();

        return $transaction;
    }

    public function deductWallet(int $userId, float $cartAmount): array
    {
        return DB::transaction(function () use ($userId, $cartAmount) {

            $wallet = Wallet::where('user_id', $userId)
                ->lockForUpdate()
                ->first();
    
            if (!$wallet) {
                throw new \Exception('Wallet not found');
            }

            /* 🔹 1. Check bonus validity */
            $isBonusValid = $wallet->bonus_valid_until
                && now()->lte($wallet->bonus_valid_until);
   
            if (!$isBonusValid) {
                $wallet->bonus_balance = 0;
                $wallet->bonus_valid_until = null;
                $wallet->wallet_offer_id = null;
            }

            /* 🔹 2. Get offer */
            $offer = $wallet->wallet_offer_id
                ? WalletOffer::find($wallet->wallet_offer_id)
                : null;
         
            $usableBonus = 0;

            /* 🔹 3. Apply bonus only if condition satisfied */
            if ($offer && $cartAmount >= $offer->item_amount && $wallet->bonus_balance > 0) {
                $usableBonus = min($wallet->bonus_balance, $cartAmount);
            }
     
            /* 4. Remaining amount */
            $remainingAmount = $cartAmount - $usableBonus;
            if ($wallet->balance < $remainingAmount) {
                throw new \Exception('Insufficient wallet balance');
            }

            /* 5. Deduct */
            $wallet->bonus_balance -= $usableBonus;
            $wallet->balance -= $remainingAmount;

            /* 6. Cleanup bonus */
            if ($wallet->bonus_balance <= 0) {
                $wallet->bonus_balance = 0;
                $wallet->bonus_valid_until = null;
                $wallet->wallet_offer_id = null;
            }

            $wallet->save();

            /* 7. Save transaction */
            $walletTransaction = new WalletTransaction();
            $walletTransaction->user_id           = $userId;
            $walletTransaction->wallet_offer_id   = $offer?->id;
            $walletTransaction->type              = 'debit';
            $walletTransaction->amount            = $remainingAmount;
            $walletTransaction->bonus_amount      = $usableBonus;
            $walletTransaction->bonus_valid_until = $wallet->bonus_valid_until;
            $walletTransaction->description       = 'Order payment via wallet';
            $walletTransaction->save();
    
            return [
                'wallet_used'    => $remainingAmount,
                'bonus_used'     => $usableBonus,
                'total_used'     => $usableBonus + $remainingAmount,
                'wallet_balance' => $wallet->balance,
            ];
        });
    }
}
