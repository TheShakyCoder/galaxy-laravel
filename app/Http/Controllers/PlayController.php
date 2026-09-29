<?php

namespace App\Http\Controllers;

use App\Models\GameServer;
use App\Models\User;
use App\Support\PlayToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * The hand-off from this site to the game: choosing a game server, the login
 * gate play.fig.limited's nginx asks on every page load, and the token the
 * game trades for a Nakama session.
 */
class PlayController extends Controller
{
    private const SESSION_KEY = 'play.server';

    /**
     * Go to the game, or pick a server first when more than one is open.
     */
    public function index(Request $request): SymfonyResponse|InertiaResponse
    {
        $servers = GameServer::query()->open()->get();

        if ($servers->count() === 1) {
            return $this->play($request, $servers->first());
        }

        return Inertia::render('Play/Servers', [
            'servers' => $servers->map(fn (GameServer $server) => [
                'slug' => $server->slug,
                'name' => $server->name,
            ]),
        ]);
    }

    public function select(Request $request, GameServer $server): SymfonyResponse
    {
        abort_unless($server->is_open, 404);

        return $this->play($request, $server);
    }

    /**
     * For play.fig.limited's nginx auth_request: 204 for a logged-in,
     * verified user, 401 otherwise. Never a redirect.
     */
    public function authCheck(Request $request): Response
    {
        return response()->noContent($this->canPlay($request->user()) ? 204 : 401);
    }

    /**
     * The game's start-up call (proxied by play.fig.limited): a play token for
     * the chosen server, and where that server's Nakama is.
     */
    public function token(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $this->canPlay($user)) {
            return response()->json(['error' => 'unauthenticated', 'login_url' => route('login')], 401);
        }

        $server = $this->chosenServer($request);

        if ($server === null) {
            return response()->json(['error' => 'choose_server', 'play_url' => route('play')], 409);
        }

        $issued = PlayToken::issue($user, $server, config('galaxy.play_token_ttl'));
        $this->recordVisit($user, $server);

        return response()->json([
            'token' => $issued['token'],
            'user_id' => $user->uuid,
            'server' => [
                'slug' => $server->slug,
                'name' => $server->name,
                'host' => $server->nakama_host,
                'port' => $server->nakama_port,
                'ssl' => $server->nakama_ssl,
                'server_key' => $server->nakama_server_key,
            ],
            'account_url' => route('dashboard'),
        ]);
    }

    /**
     * Remember the choice (read back by token()) and leave for the game site.
     * Inertia::location works for Inertia visits and plain requests alike.
     */
    private function play(Request $request, GameServer $server): SymfonyResponse
    {
        $request->session()->put(self::SESSION_KEY, $server->slug);

        return Inertia::location(config('galaxy.play_url'));
    }

    private function canPlay(?User $user): bool
    {
        return $user !== null && $user->hasVerifiedEmail();
    }

    /**
     * The server picked on this site, or the only open one.
     */
    private function chosenServer(Request $request): ?GameServer
    {
        $slug = $request->session()->get(self::SESSION_KEY);

        if ($slug !== null) {
            return GameServer::query()->open()->where('slug', $slug)->first();
        }

        $open = GameServer::query()->open()->limit(2)->get();

        return $open->count() === 1 ? $open->first() : null;
    }

    private function recordVisit(User $user, GameServer $server): void
    {
        $now = now();

        if ($user->gameServers()->whereKey($server->getKey())->exists()) {
            $user->gameServers()->updateExistingPivot($server->getKey(), ['last_played_at' => $now]);
        } else {
            $user->gameServers()->attach($server->getKey(), ['first_played_at' => $now, 'last_played_at' => $now]);
        }
    }
}
