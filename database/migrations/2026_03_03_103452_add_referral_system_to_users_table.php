<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add referral code field - unique code for each user
            $table->string('referral_code', 20)->unique()->nullable()->after('email');
            
            // Add referred_by field - stores the ID of the user who referred this user
            $table->unsignedBigInteger('referred_by')->nullable()->after('referral_code');
            
            // Add referral_coins field - coins earned from referrals
            $table->integer('referral_coins')->default(0)->after('referred_by');
            
            // Add foreign key constraint
            $table->foreign('referred_by')->references('id')->on('users')->onDelete('set null');
            
            // Add index for better query performance
            $table->index('referral_code');
            $table->index('referred_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['referred_by']);
            
            // Drop columns
            $table->dropColumn(['referral_code', 'referred_by', 'referral_coins']);
        });
    }
};
