<?php

use App\Models\Server;
use App\Models\User;

/**
 * Checks an HS256 play token against $secret and returns its claims.
 *
 * @return array<string, mixed>
 */
function verifiedPlayTokenClaims(string $token, string $secret): array
{
    [$header, $payload, $signature] = explode('.', $token);
    $expected = rtrim(strtr(base64_encode(hash_hmac('sha256', "{$header}.{$payload}", $secret, true)), '+/', '-_'), '=');

    expect($signature)->toBe($expected);

    return json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
}

test('the play gate lets verified users through', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('play.auth-check'))
        ->assertNoContent(204);
});

test('the play gate answers 401 without redirecting guests or unverified users', function (?User $user) {
    if ($user) {
        $this->actingAs($user);
    }

    $this->get(route('play.auth-check'))->assertStatus(401);
})->with([
    'guest' => fn () => null,
    'unverified' => fn () => User::factory()->unverified()->create(),
]);

test('a play token identifies the user to the chosen server', function () {
    $this->freezeTime();
    $user = User::factory()->create(['name' => 'Starbuck']);
    $server = Server::factory()->create(['slug' => 'api1', 'nakama_host' => 'api1.fig.limited']);

    $response = $this->actingAs($user)->getJson(route('play.token'));

    $response->assertOk()->assertJson([
        'user_id' => $user->uuid,
        'server' => ['slug' => 'api1', 'host' => 'api1.fig.limited', 'port' => 443, 'ssl' => true, 'server_key' => 'defaultkey'],
        'account_url' => route('dashboard'),
    ]);
    expect(verifiedPlayTokenClaims($response->json('token'), $server->play_token_secret))->toMatchArray([
        'sub' => $user->uuid,
        'aud' => 'api1',
        'name' => 'Starbuck',
        'exp' => now()->getTimestamp() + config('galaxy.play_token_ttl'),
    ]);
    $this->assertDatabaseHas('players', ['user_id' => $user->id, 'server_id' => $server->id]);
});

test('a play token is refused to guests and unverified users', function (?User $user) {
    Server::factory()->create();

    if ($user) {
        $this->actingAs($user);
    }

    $this->getJson(route('play.token'))
        ->assertUnauthorized()
        ->assertJson(['error' => 'unauthenticated', 'login_url' => route('login')])
        ->assertJsonMissingPath('token');
})->with([
    'guest' => fn () => null,
    'unverified' => fn () => User::factory()->unverified()->create(),
]);

test('with several open servers the player must choose one before getting a token', function () {
    Server::factory()->count(2)->create();

    $this->actingAs(User::factory()->create())
        ->getJson(route('play.token'))
        ->assertStatus(409)
        ->assertJson(['error' => 'choose_server', 'play_url' => route('play')]);
});

test('choosing a server sends the player to the game with a token for that server', function () {
    $user = User::factory()->create();
    Server::factory()->create(['slug' => 'alpha']);
    $beta = Server::factory()->create(['slug' => 'beta']);

    $this->actingAs($user)->get(route('play'))
        ->assertInertia(fn ($page) => $page->component('Play/Servers')->has('servers', 2));

    $this->post(route('play.select', $beta))->assertRedirect(config('galaxy.play_url'));

    $token = $this->getJson(route('play.token'))->assertOk()->json('token');
    expect(verifiedPlayTokenClaims($token, $beta->play_token_secret)['aud'])->toBe('beta');
});

test('with one open server play goes straight to the game', function () {
    Server::factory()->create();
    Server::factory()->closed()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('play'))
        ->assertRedirect(config('galaxy.play_url'));
});

test('a closed server cannot be chosen', function () {
    $server = Server::factory()->closed()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('play.select', $server))
        ->assertNotFound();
});

test('unverified users are sent to verify their email instead of playing', function () {
    Server::factory()->create();

    $this->actingAs(User::factory()->unverified()->create())
        ->get(route('play'))
        ->assertRedirect(route('verification.notice'));
});
