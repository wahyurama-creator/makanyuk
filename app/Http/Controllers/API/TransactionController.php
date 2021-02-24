<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Midtrans\Config;
use Midtrans\Snap;

class TransactionController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit', 6);
        $food_id = $request->input('food_id');
        $status = $request->input('status');

        if ($id) {
            $transaction = Transaction::with(['food', 'user'])->findOrFail($id);

            if ($transaction) {
                return ResponseFormatter::success(
                    $transaction,
                    'Transaction success retrieved'
                );
            } else {
                return ResponseFormatter::error(
                    null,
                    'Transaction is empty',
                    404
                );
            }
        }

        $transaction = Transaction::with(['food', 'user'])
            ->where('user_id', Auth::user()->id);
        if ($food_id) {
            $transaction->where('food_id', $food_id);
        }
        if ($status) {
            $transaction->where('status', $status);
        }

        return ResponseFormatter::success(
            $transaction->paginate($limit),
            'List of transaction success retrieved'
        );
    }

    public function update(Request $request, int $id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->update($request->all());
        return ResponseFormatter::success(
            $transaction,
            'Transaction success updated'
        );
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'food_id' => 'required|exists:food,id',
            'user_id' => 'required|exists:users,id',
            'quantity' => 'required',
            'total' => 'required',
            'status' => 'required'
        ]);

        $transaction = Transaction::create([
            'food_id' => $request->food_id,
            'user_id' => $request->user_id,
            'quantity' => $request->quantity,
            'total' => $request->total,
            'status' => $request->status,
            'payment_url' => ''
        ]);

        Config::$serverKey = config('services.midtrans.serverKey');
        Config::$clientKey = config('services.midtrans.clientKey');
        Config::$isProduction = config('services.midtrans.isProduction');
        Config::$is3ds = config('services.midtrans.is3ds');
        Config::$isSanitized = config('services.midtrans.isSanitized');

        $transaction = Transaction::with(['food', 'user'])->findOrFail($transaction->id);
        $midtrans = [
            'transaction_details' => [
                'order_id' => $transaction->id,
                'gross_amount' => (int)$transaction->total
            ],
            'customer_details' => [
                'first_name' => $transaction->user->name,
                'email' => $transaction->user->email
            ],
            'enabled_payments' => [
                "cimb_clicks",
                "bca_klikbca", "bca_klikpay", "bri_epay", "echannel", "permata_va",
                "bca_va", "bni_va", "bri_va", "other_va", "gopay", "indomaret",
                "danamon_online", "akulaku", "shopeepay", "bank_transfer"
            ],
            'vtweb' => []
        ];

        try {
            $paymentUrl = Snap::createTransaction($midtrans)->redirect_url;
            $transaction->payment_url = $paymentUrl;
            $transaction->save();

            return ResponseFormatter::success(
                $transaction,
                'Transaction success'
            );
        } catch (\Exception $exception) {
            return ResponseFormatter::error(
                $exception->getMessage(),
                'Transaction failed'
            );
        }
    }
}
