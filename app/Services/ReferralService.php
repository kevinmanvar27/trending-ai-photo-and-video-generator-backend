<?php

namespace App\Services;

use App\Models\Referral;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReferralService
{
    /**
     * Default coins to award per referral (fallback if setting not found)
     */
    const DEFAULT_REFERRAL_COINS = 100;
    
    /**
     * Default bonus coins for new user (fallback if setting not found)
     */
    const DEFAULT_NEW_USER_BONUS = 50;
    
    /**
     * Get referral coins per referral from settings
     */
    public static function getReferralCoinsAmount()
    {
        return (int) Setting::get('referral_coins_per_referral', self::DEFAULT_REFERRAL_COINS);
    }
    
    /**
     * Get new user bonus coins from settings
     */
    public static function getNewUserBonusAmount()
    {
        return (int) Setting::get('referral_bonus_for_new_user', self::DEFAULT_NEW_USER_BONUS);
    }
    
    /**
     * Check if referral system is enabled
     */
    public static function isReferralSystemEnabled()
    {
        return Setting::getBool('referral_system_enabled', true);
    }

    /**
     * Apply a referral code to a new user
     *
     * @param int $newUserId
     * @param string $referralCode
     * @return bool
     */
    public static function applyReferralCode($newUserId, $referralCode)
    {
        try {
            $referralCode = strtoupper(trim($referralCode));
            
            // Find the referrer
            $referrer = User::where('referral_code', $referralCode)->first();

            if (!$referrer) {
                Log::warning("Invalid referral code attempted: {$referralCode}");
                return false;
            }

            // Get the new user
            $newUser = User::find($newUserId);
            if (!$newUser) {
                Log::error("User not found: {$newUserId}");
                return false;
            }

            // Prevent self-referral
            if ($referrer->id === $newUser->id) {
                Log::warning("Self-referral attempted by user: {$newUserId}");
                return false;
            }

            // Check if user was already referred
            if ($newUser->referred_by) {
                Log::warning("User {$newUserId} was already referred by user {$newUser->referred_by}");
                return false;
            }

            DB::beginTransaction();

            // Update the new user's referred_by field
            $newUser->update(['referred_by' => $referrer->id]);

            // Create referral record
            Referral::create([
                'referrer_id' => $referrer->id,
                'referred_id' => $newUserId,
                'status' => 'pending',
            ]);

            DB::commit();

            Log::info("Referral applied: User {$newUserId} referred by user {$referrer->id}");
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error applying referral code: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Complete a referral and award coins
     *
     * @param int $userId The referred user's ID
     * @param int $coinsToAward Coins to award to the referrer (null to use setting value)
     * @return bool
     */
    public static function completeReferral($userId, $coinsToAward = null)
    {
        try {
            // Use admin-configured value from settings if not specified
            $coinsToAward = $coinsToAward ?? self::getReferralCoinsAmount();

            $user = User::find($userId);
            if (!$user || !$user->referred_by) {
                return false;
            }

            // Find the pending referral record
            $referral = Referral::where('referred_id', $userId)
                ->where('status', 'pending')
                ->first();

            if (!$referral) {
                Log::warning("No pending referral found for user: {$userId}");
                return false;
            }

            DB::beginTransaction();

            // Mark referral as completed
            $referral->update([
                'status' => 'completed',
                'coins_earned' => $coinsToAward,
                'completed_at' => now(),
            ]);

            // Award coins to the referrer
            $referrer = User::find($user->referred_by);
            if ($referrer) {
                $referrer->increment('referral_coins', $coinsToAward);
                Log::info("Awarded {$coinsToAward} coins to user {$referrer->id} for referring user {$userId}");
            }

            DB::commit();

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error completing referral: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cancel a referral
     *
     * @param int $userId The referred user's ID
     * @return bool
     */
    public static function cancelReferral($userId)
    {
        try {
            $referral = Referral::where('referred_id', $userId)
                ->whereIn('status', ['pending', 'completed'])
                ->first();

            if (!$referral) {
                return false;
            }

            DB::beginTransaction();

            // If referral was completed, deduct coins from referrer
            if ($referral->status === 'completed' && $referral->coins_earned > 0) {
                $referrer = User::find($referral->referrer_id);
                if ($referrer) {
                    $referrer->decrement('referral_coins', $referral->coins_earned);
                }
            }

            // Mark as cancelled
            $referral->update(['status' => 'cancelled']);

            DB::commit();

            Log::info("Referral cancelled for user: {$userId}");
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error cancelling referral: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get referral statistics for a user
     *
     * @param int $userId
     * @return array
     */
    public static function getUserReferralStats($userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return null;
        }

        return [
            'referral_code' => $user->referral_code,
            'total_referrals' => $user->referrals()->count(),
            'completed_referrals' => $user->referralsMade()->where('status', 'completed')->count(),
            'pending_referrals' => $user->referralsMade()->where('status', 'pending')->count(),
            'total_coins_earned' => $user->referral_coins,
        ];
    }

    /**
     * Validate a referral code
     *
     * @param string $referralCode
     * @return User|null
     */
    public static function validateReferralCode($referralCode)
    {
        $referralCode = strtoupper(trim($referralCode));
        return User::where('referral_code', $referralCode)->first();
    }

    /**
     * Redeem referral coins for subscription coins
     *
     * @param int $userId
     * @param int $coinsToRedeem
     * @return array|false
     */
    public static function redeemCoins($userId, $coinsToRedeem)
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return false;
            }

            if ($user->referral_coins < $coinsToRedeem) {
                return false;
            }

            DB::beginTransaction();

            // Deduct referral coins
            $user->decrement('referral_coins', $coinsToRedeem);

            // Add to active subscription coins
            $activeSubscription = $user->activeSubscription;
            if ($activeSubscription) {
                $activeSubscription->increment('remaining_coins', $coinsToRedeem);
            }

            DB::commit();

            Log::info("User {$userId} redeemed {$coinsToRedeem} referral coins");

            return [
                'remaining_referral_coins' => $user->referral_coins,
                'subscription_coins' => $activeSubscription ? $activeSubscription->remaining_coins : 0,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error redeeming coins: ' . $e->getMessage());
            return false;
        }
    }
}
