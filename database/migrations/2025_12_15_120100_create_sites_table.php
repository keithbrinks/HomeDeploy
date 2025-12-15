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
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('repo_url');
            $table->string('branch')->default('main');
            $table->string('deploy_path');
            $table->integer('port')->nullable();
            $table->json('build_commands')->nullable();
            $table->string('status')->default('inactive'); // active, inactive, deploying
            $table->string('nginx_config_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
