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
     * The player's account overview: every open game server, each with its
     * own Play link, plus any closed server they've played on - with what
     * they have on each (faction, ship, balances, location), read live from
     * that server. Asking a server also tells us whether it's running.
     */
    public function __invoke(Request $request, NakamaClient $nakama): Response
    {
        $user = $request->user();
        $joined = $user->servers()->get()->keyBy('id');

        $servers = Server::query()
            ->where(fn ($query) => $query->where('is_open', true)->orWhereIn('id', $joined->keys()))
            ->orderBy('sort')->orderBy('name')->get()
            ->map(function (Server $server) use ($nakama, $user, $joined) {
                $summaries = $nakama->playerSummaries($server, [$user->uuid]);
                $visit = $joined->get($server->id)?->pivot;

                return [
                    'slug' => $server->slug,
                    'name' => $server->name,
                    'is_open' => $server->is_open,
                    'joined' => $visit !== null,
                    'first_played_at' => $visit?->first_played_at,
                    'last_played_at' => $visit?->last_played_at,
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
