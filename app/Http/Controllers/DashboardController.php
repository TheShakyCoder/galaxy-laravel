<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Services\NakamaClient;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * The player's account overview: a Play button, and what they have on
     * each game server they've joined (faction, ship, balances, location),
     * read live from that server.
     */
    public function __invoke(Request $request, NakamaClient $nakama): Response
    {
        $user = $request->user();

        $servers = $user->servers()->orderBy('sort')->orderBy('name')->get()
            ->map(function (Server $server) use ($nakama, $user) {
                $summaries = $nakama->playerSummaries($server, [$user->uuid]);

                return [
                    'slug' => $server->slug,
                    'name' => $server->name,
                    'is_open' => $server->is_open,
                    'first_played_at' => $server->pivot->first_played_at,
                    'last_played_at' => $server->pivot->last_played_at,
                    'reachable' => $summaries !== null,
                    'summary' => $summaries[$user->uuid] ?? null,
                ];
            });

        return Inertia::render('Dashboard', [
            'canPlay' => Server::query()->open()->exists(),
            'servers' => $servers,
            'gameVersion' => config('app.version'),
        ]);
    }
}
