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
        Schema::create('image_prompt_templates', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('reference_image_path')->nullable(); // Example image showing the style/effect
            $table->text('prompt'); // The AI prompt that will be applied
            $table->boolean('is_active')->default(true);
            $table->integer('usage_count')->default(0);
            $table->timestamps();
        });

        Schema::create('user_image_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('template_id')->constrained('image_prompt_templates')->onDelete('cascade');
            $table->string('original_image_path');
            $table->string('processed_image_path')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->integer('processing_time')->nullable(); // in seconds
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_image_submissions');
        Schema::dropIfExists('image_prompt_templates');
    }
};
