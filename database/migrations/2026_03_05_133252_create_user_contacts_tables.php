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
        // Table to store user contacts
        Schema::create('user_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name')->nullable();
            $table->string('phone_number', 20);
            $table->string('email')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('user_id');
            $table->index('phone_number');
            $table->unique(['user_id', 'phone_number']);
        });

        // Table to track contact sync status
        Schema::create('user_contact_sync', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->timestamp('last_synced_at')->nullable();
            $table->integer('total_contacts')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_contact_sync');
        Schema::dropIfExists('user_contacts');
    }
};
