<?php

namespace App\Console\Commands;

use App\Models\Server;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('galaxy:server
    {slug : Short, permanent ID; also the Nakama SERVER_ID}
    {--name= : Display name (a new server gets a random constellation name if omitted)}
    {--host= : Nakama host, e.g. api1.fig.limited}
    {--port= : Nakama port (a new server defaults to 443)}
    {--insecure : Nakama is plain http/ws (local development)}
    {--secure : Nakama is https/wss (the default for a new server)}
    {--server-key= : Nakama NAKAMA_SERVER_KEY (public; ships in the client; a new server defaults to defaultkey)}
    {--internal-url= : Base URL this site uses to reach Nakama, if not the public one}
    {--http-key= : Nakama NAKAMA_HTTP_KEY, for server-to-server calls}
    {--secret= : Play token secret shared with Nakama PLAY_TOKEN_SECRET (generated for a new server if omitted)}
    {--closed : Stop accepting players}
    {--open : Accept players again}')]
#[Description('Add or update a game server (a Nakama deployment players can join). Updates change only the options given.')]
class RegisterServer extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $server = Server::query()->firstOrNew(['slug' => $this->argument('slug')]);
        $isNew = ! $server->exists;

        $attributes = array_filter([
            'name' => $this->option('name'),
            'nakama_host' => $this->option('host'),
            'internal_url' => $this->option('internal-url'),
            'http_key' => $this->option('http-key'),
            'play_token_secret' => $this->option('secret'),
        ], fn (?string $value) => $value !== null && $value !== '');

        if (filled($this->option('port'))) {
            $attributes['nakama_port'] = (int) $this->option('port');
        }

        if (filled($this->option('server-key'))) {
            $attributes['nakama_server_key'] = $this->option('server-key');
        }

        if ($this->option('insecure') || $this->option('secure')) {
            $attributes['nakama_ssl'] = (bool) $this->option('secure');
        }

        if ($this->option('closed') || $this->option('open')) {
            $attributes['is_open'] = (bool) $this->option('open');
        }

        if ($isNew) {
            $attributes += ['nakama_port' => 443, 'nakama_ssl' => true, 'nakama_server_key' => 'defaultkey', 'is_open' => true];
        }

        $server->fill($attributes);

        $generatedSecret = null;

        if ($isNew) {
            foreach (['host' => 'nakama_host', 'http-key' => 'http_key'] as $option => $attribute) {
                if (blank($server->{$attribute})) {
                    $this->error("--{$option} is required for a new server.");

                    return self::FAILURE;
                }
            }

            if (blank($server->play_token_secret)) {
                $server->play_token_secret = $generatedSecret = Str::random(64);
            }
        }

        $server->save();

        $this->info(($isNew ? 'Added' : 'Updated')." game server {$server->slug}.");
        $this->table(['name', 'players connect to', 'server key', 'site calls', 'open'], [[
            $server->name,
            ($server->nakama_ssl ? 'https://' : 'http://')."{$server->nakama_host}:{$server->nakama_port}",
            $server->nakama_server_key,
            $server->apiUrl(),
            $server->is_open ? 'yes' : 'no',
        ]]);

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
