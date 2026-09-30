<?php

use App\Models\Server;
use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

test('the dashboard shows the player\'s pilot on each server they joined', function () {
    Http::preventStrayRequests();
    $user = User::factory()->create();
    $server = Server::factory()->create(['name' => 'Api One', 'nakama_host' => 'api1.test', 'http_key' => 'the-http-key']);
    $user->servers()->attach($server, ['first_played_at' => now(), 'last_played_at' => now()]);
    Http::fake([
        'https://api1.test/v2/rpc/player_summary*' => Http::response(['players' => [
            $user->uuid => ['faction_name' => 'The Swarm', 'ship_name' => 'Patrol Interceptor', 'scrip' => 1000, 'hydrogen' => 5000, 'system_name' => 'Polaris'],
        ]]),
    ]);

    $this->actingAs($user)->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('canPlay', true)
            ->where('servers.0.name', 'Api One')
            ->where('servers.0.reachable', true)
            ->where('servers.0.summary.faction_name', 'The Swarm')
            ->where('servers.0.summary.scrip', 1000));

    Http::assertSent(fn (Request $request) => $request['user_ids'] === [$user->uuid]
        && str_contains($request->url(), 'http_key=the-http-key'));
});

test('an unreachable server is shown as unreachable rather than failing the page', function () {
    $user = User::factory()->create();
    $server = Server::factory()->create(['nakama_host' => 'down.test']);
    $user->servers()->attach($server, ['first_played_at' => now(), 'last_played_at' => now()]);
    Http::fake(['https://down.test/*' => Http::response('Bad gateway', 502)]);

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('servers.0.reachable', false)
            ->where('servers.0.summary', null));
});

test('deleting an account deletes the player on every server they joined', function () {
    Http::preventStrayRequests();
    $user = User::factory()->create();
    $server = Server::factory()->create(['nakama_host' => 'api1.test']);
    $user->servers()->attach($server, ['first_played_at' => now(), 'last_played_at' => now()]);
    $uuid = $user->uuid;
    Http::fake(['https://api1.test/v2/rpc/delete_player*' => Http::response([])]);

    $this->actingAs($user)->delete('/profile', ['password' => 'password'])->assertRedirect('/');

    $this->assertNull($user->fresh());
    Http::assertSent(fn (Request $request) => str_contains($request->url(), '/v2/rpc/delete_player') && $request['user_id'] === $uuid);
});
