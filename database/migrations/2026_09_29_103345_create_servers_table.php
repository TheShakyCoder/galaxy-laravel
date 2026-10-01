<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('nakama_host');
            $table->unsignedInteger('nakama_port')->default(443);
            $table->boolean('nakama_ssl')->default(true);
            $table->string('nakama_server_key');
            $table->string('internal_url')->nullable();
            $table->text('play_token_secret');
            $table->text('http_key');
            $table->boolean('is_open')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servers');
    }
};
