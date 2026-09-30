<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * GameServer became Server: game_servers -> servers, and the pivot
 * game_server_user (game_server_id) -> server_user (server_id). Existing rows
 * are kept. The foreign keys and unique index are recreated so their names
 * match the new tables.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('game_server_user', function (Blueprint $table) {
            $table->dropForeign(['game_server_id']);
            $table->dropForeign(['user_id']);
            $table->dropUnique(['game_server_id', 'user_id']);
        });

        Schema::rename('game_servers', 'servers');
        Schema::rename('game_server_user', 'server_user');

        Schema::table('server_user', function (Blueprint $table) {
            $table->renameColumn('game_server_id', 'server_id');
        });

        Schema::table('server_user', function (Blueprint $table) {
            $table->foreign('server_id')->references('id')->on('servers')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['server_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('server_user', function (Blueprint $table) {
            $table->dropForeign(['server_id']);
            $table->dropForeign(['user_id']);
            $table->dropUnique(['server_id', 'user_id']);
        });

        Schema::rename('servers', 'game_servers');
        Schema::rename('server_user', 'game_server_user');

        Schema::table('game_server_user', function (Blueprint $table) {
            $table->renameColumn('server_id', 'game_server_id');
        });

        Schema::table('game_server_user', function (Blueprint $table) {
            $table->foreign('game_server_id')->references('id')->on('game_servers')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['game_server_id', 'user_id']);
        });
    }
};
