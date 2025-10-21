<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_field_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_id')->constrained('custom_fields')->onDelete('cascade');
            $table->string('locale', 10);
            $table->string('name');
            $table->json('options')->nullable();
            $table->timestamps();

            $table->unique(['field_id', 'locale']);
            $table->index('locale');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_field_translations');
    }
};
