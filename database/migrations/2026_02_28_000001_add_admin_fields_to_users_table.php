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
            $table->enum('role', ['user', 'admin'])->default('user')->after('email');
            $table->boolean('is_suspended')->default(false)->after('role');
            $table->timestamp('suspended_at')->nullable()->after('is_suspended');
            $table->text('suspension_reason')->nullable()->after('suspended_at');
            $table->integer('total_time_spent')->default(0)->comment('Total time spent in seconds')->after('suspension_reason');
            $table->timestamp('last_activity_at')->nullable()->after('total_time_spent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'is_suspended',
                'suspended_at',
                'suspension_reason',
                'total_time_spent',
                'last_activity_at'
            ]);
        });
    }
};
