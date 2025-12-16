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
            $table->string('name')->unique();
            $table->string('domain')->nullable();
            $table->enum('domain_strategy', ['subdomain', 'custom'])->default('subdomain');
            $table->string('repo_url');
            $table->string('branch')->default('main');
            $table->string('deploy_path');
            $table->integer('port')->nullable();
            $table->text('github_token')->nullable();
            $table->text('webhook_secret')->nullable();
            $table->string('database_name')->nullable();
            $table->string('database_username')->nullable();
            $table->text('database_password')->nullable();
            $table->json('build_commands')->nullable();
            $table->string('status')->default('inactive');
            $table->string('nginx_config_path')->nullable();
            $table->timestamps();
            
            $table->index('name');
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
