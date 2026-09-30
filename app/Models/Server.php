<?php

namespace App\Models;

use App\Support\Constellations;
use Database\Factories\ServerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * A Nakama deployment that hosts one game world. Players log in to it with a
 * short-lived token signed with its play_token_secret (see PlayToken), and
 * the site calls its server-only RPCs with its http_key (see NakamaClient).
 */
#[Fillable(['slug', 'name', 'nakama_host', 'nakama_port', 'nakama_ssl', 'nakama_server_key', 'internal_url', 'play_token_secret', 'http_key', 'is_open', 'sort'])]
#[Hidden(['play_token_secret', 'http_key'])]
class Server extends Model
{
    /** @use HasFactory<ServerFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nakama_port' => 'integer',
            'nakama_ssl' => 'boolean',
            'play_token_secret' => 'encrypted',
            'http_key' => 'encrypted',
            'is_open' => 'boolean',
            'sort' => 'integer',
        ];
    }

    /**
     * A server created without a name gets a random constellation's, one no
     * other server has.
     */
    protected static function booted(): void
    {
        static::creating(function (Server $server): void {
            if (blank($server->name)) {
                $server->name = Constellations::unusedName(static::query()->pluck('name')->all());
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function players(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('first_played_at', 'last_played_at');
    }

    /**
     * @param  Builder<Server>  $query
     */
    public function scopeOpen(Builder $query): void
    {
        $query->where('is_open', true)->orderBy('sort')->orderBy('name');
    }

    /**
     * The base URL this site uses for server-to-server calls: internal_url
     * when this site reaches Nakama another way than players do (a private
     * network, or host.docker.internal in local development), otherwise the
     * public address.
     */
    public function apiUrl(): string
    {
        if (filled($this->internal_url)) {
            return rtrim($this->internal_url, '/');
        }

        $scheme = $this->nakama_ssl ? 'https' : 'http';
        $defaultPort = $this->nakama_ssl ? 443 : 80;
        $port = $this->nakama_port === $defaultPort ? '' : ':'.$this->nakama_port;

        return "{$scheme}://{$this->nakama_host}{$port}";
    }
}
