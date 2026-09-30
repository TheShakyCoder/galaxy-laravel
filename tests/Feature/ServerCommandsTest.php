<?php

use App\Models\Server;
use App\Models\User;
use App\Support\Constellations;

test('registering a new server generates the shared play token secret', function () {
    $this->artisan('galaxy:server', ['slug' => 'api1', '--host' => 'api1.fig.limited', '--http-key' => 'secret-http-key'])
        ->expectsOutputToContain('SERVER_ID=api1')
        ->expectsOutputToContain('PLAY_TOKEN_SECRET=')
        ->assertSuccessful();

    $server = Server::query()->where('slug', 'api1')->sole();
    expect($server->apiUrl())->toBe('https://api1.fig.limited')
        ->and($server->http_key)->toBe('secret-http-key')
        ->and(strlen($server->play_token_secret))->toBe(64);
});

test('a new server without a name gets a constellation name no other server has', function () {
    $taken = Server::factory()->create(['name' => 'Orion']);

    $this->artisan('galaxy:server', ['slug' => 'api2', '--host' => 'api2.fig.limited', '--http-key' => 'key'])->assertSuccessful();

    $name = Server::query()->where('slug', 'api2')->value('name');
    expect($name)->toBeIn(Constellations::NAMES)->not->toBe($taken->name);
});

test('a name given when adding a server is used', function () {
    $this->artisan('galaxy:server', ['slug' => 'api1', '--host' => 'api1.fig.limited', '--http-key' => 'key', '--name' => 'Caprica'])->assertSuccessful();

    expect(Server::query()->where('slug', 'api1')->value('name'))->toBe('Caprica');
});

test('server-to-server calls use the internal url when one is set', function () {
    $this->artisan('galaxy:server', ['slug' => 'local', '--host' => '127.0.0.1', '--port' => 7350, '--insecure' => true, '--http-key' => 'key', '--internal-url' => 'http://host.docker.internal:7350/'])
        ->assertSuccessful();

    expect(Server::query()->where('slug', 'local')->sole()->apiUrl())->toBe('http://host.docker.internal:7350');
});

test('a new server needs a host and http key', function () {
    $this->artisan('galaxy:server', ['slug' => 'api1'])->assertFailed();

    expect(Server::query()->count())->toBe(0);
});

test('updating a server changes only the options given', function () {
    $server = Server::factory()->create([
        'slug' => 'api1', 'play_token_secret' => 'kept-secret', 'nakama_server_key' => 'kept-key',
        'nakama_port' => 7350, 'nakama_ssl' => false,
    ]);

    $this->artisan('galaxy:server', ['slug' => 'api1', '--closed' => true])->assertSuccessful();

    expect($server->fresh())
        ->is_open->toBeFalse()
        ->play_token_secret->toBe('kept-secret')
        ->nakama_server_key->toBe('kept-key')
        ->nakama_port->toBe(7350)
        ->nakama_ssl->toBeFalse();

    $this->artisan('galaxy:server', ['slug' => 'api1', '--open' => true, '--server-key' => 'new-key', '--secure' => true])->assertSuccessful();

    expect($server->fresh())->is_open->toBeTrue()->nakama_server_key->toBe('new-key')->nakama_ssl->toBeTrue()->nakama_port->toBe(7350);
});

test('dev play tokens are only issued in the local environment', function () {
    $user = User::factory()->create();
    Server::factory()->create(['slug' => 'local']);

    $this->artisan('galaxy:dev-token', ['email' => $user->email, 'server' => 'local'])->assertFailed();

    app()->detectEnvironment(fn () => 'local');
    $this->artisan('galaxy:dev-token', ['email' => $user->email, 'server' => 'local'])->assertSuccessful();
});
