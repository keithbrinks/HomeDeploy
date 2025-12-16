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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('github_client_id')->nullable();
            $table->text('github_client_secret')->nullable();
            $table->string('github_redirect_uri')->nullable();
            $table->text('github_token')->nullable();
            $table->string('github_user')->nullable();
            
            // Server configuration
            $table->string('server_ip')->nullable(); // Server's public/local IP address
            $table->string('base_domain')->nullable(); // Base domain for sites (e.g., example.com)
            
            // Global Cloudflare Tunnel
            $table->text('cloudflare_tunnel_token')->nullable();
            $table->string('cloudflare_tunnel_id')->nullable();
            $table->boolean('cloudflare_tunnel_enabled')->default(false);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
