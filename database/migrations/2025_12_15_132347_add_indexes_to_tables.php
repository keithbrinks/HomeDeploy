<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deployments', function (Blueprint $table) {
            $table->index('site_id');
            $table->index('status');
            $table->index(['site_id', 'created_at']);
        });

        Schema::table('environment_variables', function (Blueprint $table) {
            $table->index('site_id');
        });

        Schema::table('sites', function (Blueprint $table) {
            $table->unique('name');
        });
    }

    public function down(): void
    {
        Schema::table('deployments', function (Blueprint $table) {
            $table->dropIndex(['site_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['site_id', 'created_at']);
        });

        Schema::table('environment_variables', function (Blueprint $table) {
            $table->dropIndex(['site_id']);
        });

        Schema::table('sites', function (Blueprint $table) {
            $table->dropUnique(['name']);
        });
    }
};
