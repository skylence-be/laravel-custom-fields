<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_field_activity_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_id')->constrained('custom_fields')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event'); // 'created', 'updated', 'deleted', 'restored', 'force_deleted'
            $table->json('old_values')->nullable(); // JSON of old attribute values
            $table->json('new_values')->nullable(); // JSON of new attribute values
            $table->json('changed_attributes')->nullable(); // Array of which attributes changed
            $table->text('description')->nullable(); // Human-readable description
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['field_id', 'created_at']);
            $table->index('user_id');
            $table->index('event');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_field_activity_log');
    }
};
