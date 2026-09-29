<?php

namespace App\Console\Commands;

use App\Models\GameServer;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('galaxy:server
    {slug : Short, permanent ID; also the Nakama SERVER_ID}
    {--name= : Display name}
    {--host= : Nakama host, e.g. api1.fig.limited}
    {--port=443 : Nakama port}
    {--insecure : Nakama is plain http/ws (local development)}
    {--server-key=defaultkey : Nakama server key (public; ships in the client)}
    {--internal-url= : Base URL this site uses to reach Nakama, if not the public one}
    {--http-key= : Nakama NAKAMA_HTTP_KEY, for server-to-server calls}
    {--secret= : Play token secret shared with Nakama PLAY_TOKEN_SECRET (generated for a new server if omitted)}
    {--closed : Stop accepting players}')]
#[Description('Add or update a game server (a Nakama deployment players can join)')]
class RegisterGameServer extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $server = GameServer::query()->firstOrNew(['slug' => $this->argument('slug')]);
        $isNew = !$server->exists;

        $attributes = array_filter([
            'name' => $this->option('name'),
            'nakama_host' => $this->option('host'),
            'internal_url' => $this->option('internal-url'),
            'http_key' => $this->option('http-key'),
            'play_token_secret' => $this->option('secret'),
        ], fn(?string $value) => $value !== null && $value !== '');

        $server->fill($attributes);
        $server->fill([
            'nakama_port' => (int) $this->option('port'),
            'nakama_ssl' => !$this->option('insecure'),
            'nakama_server_key' => $this->option('server-key'),
            'is_open' => !$this->option('closed'),
        ]);

        $generatedSecret = null;

        if ($isNew) {
            foreach (['host' => 'nakama_host', 'http-key' => 'http_key'] as $option => $attribute) {
                if (blank($server->{$attribute})) {
                    $this->error("--{$option} is required for a new server.");

                    return self::FAILURE;
                }
            }

            $server->name ??= Str::headline($server->slug);

            if (blank($server->play_token_secret)) {
                $server->play_token_secret = $generatedSecret = Str::random(64);
            }
        }

        $server->save();

        $this->info(($isNew ? 'Added' : 'Updated') . " game server {$server->slug} ({$server->apiUrl()}).");

        if ($generatedSecret !== null) {
            $this->newLine();
            $this->line('Set these on that Nakama deployment:');
            $this->line('');
            $this->line("  SERVER_ID={$server->slug}");
            $this->line('');
            $this->line("  PLAY_TOKEN_SECRET={$generatedSecret}");
            $this->line('');
        }

        return self::SUCCESS;
    }
}
