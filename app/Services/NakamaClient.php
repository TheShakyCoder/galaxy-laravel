<?php

namespace App\Services;

use App\Models\GameServer;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Server-to-server calls to a game server's server-only RPCs
 * (nakama-server/modules/players.lua), authenticated with its http_key.
 */
class NakamaClient
{
    /**
     * Game data for each of the given users on $server, keyed by UUID. Users
     * who have never joined it are missing. Null when the server can't be
     * reached.
     *
     * @param  list<string>  $uuids
     * @return array<string, array<string, mixed>>|null
     */
    public function playerSummaries(GameServer $server, array $uuids): ?array
    {
        try {
            return $this->rpc($server, 'player_summary', ['user_ids' => $uuids])['players'] ?? [];
        } catch (ConnectionException|RequestException $exception) {
            Log::warning("Game server {$server->slug} player_summary failed: {$exception->getMessage()}");

            return null;
        }
    }

    /**
     * Deletes the user's account and game data on $server. Returns false if
     * the server couldn't be reached or refused.
     */
    public function deletePlayer(GameServer $server, string $uuid): bool
    {
        try {
            $this->rpc($server, 'delete_player', ['user_id' => $uuid]);

            return true;
        } catch (ConnectionException|RequestException $exception) {
            Log::warning("Game server {$server->slug} delete_player failed for {$uuid}: {$exception->getMessage()}");

            return false;
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    private function rpc(GameServer $server, string $id, array $payload): array
    {
        return Http::baseUrl($server->apiUrl())
            ->timeout(5)
            ->withQueryParameters(['http_key' => $server->http_key, 'unwrap' => ''])
            ->acceptJson()
            ->post("/v2/rpc/{$id}", $payload)
            ->throw()
            ->json() ?? [];
    }
}
