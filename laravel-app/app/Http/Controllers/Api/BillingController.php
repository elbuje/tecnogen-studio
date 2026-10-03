<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CreditLedger;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function getBalance(Request $request)
    {
        $user = $request->user();
        $recentLedger = CreditLedger::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'credits_balance' => $user->credits_balance,
            'plan_tier' => $user->plan_tier,
            'plan_renewal_date' => $user->plan_renewal_date,
            'history' => $recentLedger->map(function ($entry) {
                return [
                    'id' => $entry->id,
                    'amount' => $entry->amount,
                    'action_type' => $entry->action_type,
                    'description' => $entry->description,
                    'created_at' => $entry->created_at,
                ];
            }),
        ]);
    }
}
