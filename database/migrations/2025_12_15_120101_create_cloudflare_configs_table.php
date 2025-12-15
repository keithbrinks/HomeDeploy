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
        Schema::create('cloudflare_configs', function (Blueprint $table) {
            $table->id();
            $table->text('tunnel_token')->nullable(); // Encrypted
            $table->string('tunnel_name')->nullable();
            $table->json('routes')->nullable(); // [{hostname, service_url}]
            $table->string('status')->default('inactive'); // active, inactive
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloudflare_configs');
    }
};
