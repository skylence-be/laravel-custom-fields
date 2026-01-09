<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('custom_fields', function (Blueprint $table) {
            $table->boolean('is_system')->default(false)->after('customizable_type');
            $table->boolean('is_required')->default(false)->after('is_system');
            $table->boolean('show_in_api')->default(true)->after('is_required');
            $table->boolean('api_required')->default(false)->after('show_in_api');
        });
    }

    public function down(): void
    {
        Schema::table('custom_fields', function (Blueprint $table) {
            $table->dropColumn(['is_system', 'is_required', 'show_in_api', 'api_required']);
        });
    }
};
