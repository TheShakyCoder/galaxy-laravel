<?php

use App\Models\GameServer;
use App\Models\User;

test('registering a new server generates the shared play token secret', function () {
    $this->artisan('galaxy:server', ['slug' => 'api1', '--host' => 'api1.fig.limited', '--http-key' => 'secret-http-key'])
        ->expectsOutputToContain('SERVER_ID=api1')
        ->expectsOutputToContain('PLAY_TOKEN_SECRET=')
        ->assertSuccessful();

    $server = GameServer::query()->where('slug', 'api1')->sole();
    expect($server->apiUrl())->toBe('https://api1.fig.limited')
        ->and($server->http_key)->toBe('secret-http-key')
        ->and(strlen($server->play_token_secret))->toBe(64);
});

test('server-to-server calls use the internal url when one is set', function () {
    $this->artisan('galaxy:server', ['slug' => 'local', '--host' => '127.0.0.1', '--port' => 7350, '--insecure' => true, '--http-key' => 'key', '--internal-url' => 'http://host.docker.internal:7350/'])
        ->assertSuccessful();

    expect(GameServer::query()->where('slug', 'local')->sole()->apiUrl())->toBe('http://host.docker.internal:7350');
});

test('a new server needs a host and http key', function () {
    $this->artisan('galaxy:server', ['slug' => 'api1'])->assertFailed();

    expect(GameServer::query()->count())->toBe(0);
});

test('updating a server keeps its secrets unless new ones are given', function () {
    $server = GameServer::factory()->create(['slug' => 'api1', 'play_token_secret' => 'kept-secret']);

    $this->artisan('galaxy:server', ['slug' => 'api1', '--closed' => true])->assertSuccessful();

    expect($server->fresh())->play_token_secret->toBe('kept-secret')->is_open->toBeFalse();
});

test('dev play tokens are only issued in the local environment', function () {
    $user = User::factory()->create();
    GameServer::factory()->create(['slug' => 'local']);

    $this->artisan('galaxy:dev-token', ['email' => $user->email, 'server' => 'local'])->assertFailed();

    app()->detectEnvironment(fn () => 'local');
    $this->artisan('galaxy:dev-token', ['email' => $user->email, 'server' => 'local'])->assertSuccessful();
});
