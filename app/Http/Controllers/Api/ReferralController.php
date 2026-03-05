<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\User;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReferralController extends Controller
{
    /**
     * Get user's referral information
     */
    public function getReferralInfo(Request $request)
    {
        $user = $request->user();

        // Get referral statistics
        $totalReferrals = $user->referrals()->count();
        $completedReferrals = $user->referralsMade()->where('status', 'completed')->count();
        $pendingReferrals = $user->referralsMade()->where('status', 'pending')->count();
        $totalCoinsEarned = $user->referral_coins;

        return response()->json([
            'success' => true,
            'data' => [
                'referral_code' => $user->referral_code,
                'referral_link' => url('/register?ref=' . $user->referral_code),
                'total_referrals' => $totalReferrals,
                'completed_referrals' => $completedReferrals,
                'pending_referrals' => $pendingReferrals,
                'total_coins_earned' => $totalCoinsEarned,
                'referral_coins' => $totalCoinsEarned, // For backward compatibility
            ],
        ]);
    }

    /**
     * Get list of users referred by the current user
     */
    public function getReferralList(Request $request)
    {
        $user = $request->user();

        $referrals = $user->referralsMade()
            ->with('referred:id,name,email,created_at')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $referrals->map(function ($referral) {
                return [
                    'id' => $referral->id,
                    'user' => [
                        'id' => $referral->referred->id,
                        'name' => $referral->referred->name,
                        'email' => $referral->referred->email,
                        'joined_at' => $referral->referred->created_at,
                    ],
                    'status' => $referral->status,
                    'coins_earned' => $referral->coins_earned,
                    'completed_at' => $referral->completed_at,
                    'created_at' => $referral->created_at,
                ];
            }),
            'pagination' => [
                'current_page' => $referrals->currentPage(),
                'last_page' => $referrals->lastPage(),
                'per_page' => $referrals->perPage(),
                'total' => $referrals->total(),
            ],
        ]);
    }

    /**
     * Validate a referral code
     */
    public function validateReferralCode(Request $request)
    {
        $request->validate([
            'referral_code' => 'required|string|max:20',
        ]);

        $user = ReferralService::validateReferralCode($request->referral_code);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid referral code',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Valid referral code',
            'data' => [
                'referrer_name' => $user->name,
                'referral_code' => $user->referral_code,
            ],
        ]);
    }

    /**
     * Get referral statistics (for admin or analytics)
     */
    public function getReferralStats(Request $request)
    {
        $user = $request->user();

        // Get detailed statistics
        $stats = [
            'total_referrals' => $user->referrals()->count(),
            'completed_referrals' => $user->referralsMade()->where('status', 'completed')->count(),
            'pending_referrals' => $user->referralsMade()->where('status', 'pending')->count(),
            'total_coins_earned' => $user->referral_coins,
            'recent_referrals' => $user->referralsMade()
                ->with('referred:id,name,email,created_at')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($referral) {
                    return [
                        'user_name' => $referral->referred->name,
                        'status' => $referral->status,
                        'coins_earned' => $referral->coins_earned,
                        'date' => $referral->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Apply a referral code to the current user
     */
    public function applyReferralCode(Request $request)
    {
        $request->validate([
            'referral_code' => 'required|string|max:20',
        ]);

        $user = $request->user();

        // Check if referral system is enabled
        if (!ReferralService::isReferralSystemEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Referral system is currently disabled',
            ], 403);
        }

        // Check if user already has a referrer
        if ($user->referred_by) {
            return response()->json([
                'success' => false,
                'message' => 'You have already applied a referral code',
            ], 400);
        }

        // Apply the referral code
        $result = ReferralService::applyReferralCode($user->id, $request->referral_code);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid referral code or unable to apply',
            ], 400);
        }

        // Award new user bonus if enabled
        $bonusAmount = ReferralService::getNewUserBonusAmount();
        if ($bonusAmount > 0) {
            $user->increment('referral_coins', $bonusAmount);
        }

        return response()->json([
            'success' => true,
            'message' => 'Referral code applied successfully',
            'data' => [
                'bonus_coins_awarded' => $bonusAmount,
                'total_referral_coins' => $user->fresh()->referral_coins,
            ],
        ]);
    }

    /**
     * Redeem referral coins (convert to subscription coins or other benefits)
     */
    public function redeemCoins(Request $request)
    {
        $request->validate([
            'coins' => 'required|integer|min:1',
        ]);

        $result = ReferralService::redeemCoins($request->user()->id, $request->coins);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient referral coins or redemption failed',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Coins redeemed successfully',
            'data' => $result,
        ]);
    }
}
