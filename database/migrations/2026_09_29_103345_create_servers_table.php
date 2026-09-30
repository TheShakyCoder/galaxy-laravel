<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Game servers (App\Models\Server) and which users have played on each
 * (the server_user pivot).
 *
 * This replaced four earlier migrations (create_game_servers,
 * create_game_server_user, add_uuid_to_users and rename_game_servers_to_servers)
 * when they were squashed to one migration per model. A database that ran
 * those already has these tables: there it only renames the slug index left
 * from game_servers and forgets the old migrations.
 */
return new class extends Migration
{
    /**
     * The squashed migrations this one replaces.
     *
     * @var list<string>
     */
    private const REPLACED = [
        '2026_09_29_103344_add_uuid_to_users_table',
        '2026_09_29_103345_create_game_servers_table',
        '2026_09_29_103346_create_game_server_user_table',
        '2026_09_30_150947_rename_game_servers_to_servers',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('servers')) {
            // Left over from when this table was game_servers.
            if (Schema::hasIndex('servers', 'game_servers_slug_unique')) {
                Schema::table('servers', function (Blueprint $table) {
                    $table->renameIndex('game_servers_slug_unique', 'servers_slug_unique');
                });
            }

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

        Schema::create('server_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('first_played_at');
            $table->timestamp('last_played_at');
            $table->unique(['server_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('server_user');
        Schema::dropIfExists('servers');
    }
};
