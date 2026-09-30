<?php

namespace App\Support;

use App\Models\Server;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Signs the short-lived token a player presents to a game server's Nakama
 * (AuthenticateCustom, verified by nakama-server/modules/auth.lua): an HS256
 * JWT whose subject is the user's public UUID and whose audience is the
 * server's slug, signed with that server's own play_token_secret.
 */
class PlayToken
{
    /**
     * @return array{token: string, claims: array{sub: string, aud: string, name: string, iat: int, exp: int, jti: string}}
     */
    public static function issue(User $user, Server $server, int $ttlSeconds): array
    {
        $now = now()->getTimestamp();
        $claims = [
            'sub' => $user->uuid,
            'aud' => $server->slug,
            'name' => $user->name,
            'iat' => $now,
            'exp' => $now + $ttlSeconds,
            'jti' => (string) Str::uuid7(),
        ];

        return ['token' => self::sign($claims, $server->play_token_secret), 'claims' => $claims];
    }

    /**
     * @param  array<string, mixed>  $claims
     */
    public static function sign(array $claims, string $secret): string
    {
        $header = self::base64url(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = self::base64url(json_encode($claims));
        $signature = self::base64url(hash_hmac('sha256', "{$header}.{$payload}", $secret, true));

        return "{$header}.{$payload}.{$signature}";
    }

    private static function base64url(string $bytes): string
    {
        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }
}
