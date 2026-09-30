<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Game servers (App\Models\Server) and players: which users have played on
 * which server (the players pivot between users and servers).
 *
 * One migration per model: this replaces the earlier migrations listed in
 * REPLACED. A database that ran those already has the tables; there it only
 * brings them up to date (the slug index left from game_servers, and the
 * pivot's old name server_user) and forgets the old migrations.
 */
return new class extends Migration
{
    /**
     * The earlier migrations this one replaces.
     *
     * @var list<string>
     */
    private const REPLACED = [
        '2026_09_29_103344_add_uuid_to_users_table',
        '2026_09_29_103345_create_game_servers_table',
        '2026_09_29_103346_create_game_server_user_table',
        '2026_09_30_150947_rename_game_servers_to_servers',
        '2026_09_29_103345_create_servers_table',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('servers')) {
            $this->upgradeExistingTables();
            DB::table('migrations')->whereIn('migration', self::REPLACED)->delete();

            return;
        }

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

        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('first_played_at');
            $table->timestamp('last_played_at');
            $table->unique(['server_id', 'user_id']);
        });
    }

    /**
     * Brings tables made by the replaced migrations to this migration's
     * shape, keeping their rows.
     */
    private function upgradeExistingTables(): void
    {
        if (Schema::hasIndex('servers', 'game_servers_slug_unique')) {
            Schema::table('servers', function (Blueprint $table) {
                $table->renameIndex('game_servers_slug_unique', 'servers_slug_unique');
            });
        }

        if (Schema::hasTable('server_user')) {
            Schema::table('server_user', function (Blueprint $table) {
                $table->dropForeign(['server_id']);
                $table->dropForeign(['user_id']);
                $table->dropUnique(['server_id', 'user_id']);
            });

            Schema::rename('server_user', 'players');

            Schema::table('players', function (Blueprint $table) {
                $table->foreign('server_id')->references('id')->on('servers')->cascadeOnDelete();
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->unique(['server_id', 'user_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('players');
        Schema::dropIfExists('servers');
    }
};
