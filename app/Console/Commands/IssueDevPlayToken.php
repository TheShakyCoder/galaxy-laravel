<?php

namespace App\Console\Commands;

use App\Models\Server;
use App\Models\User;
use App\Support\PlayToken;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('galaxy:dev-token
    {email : A verified user}
    {server : Game server slug}
    {--days=30 : How long the token is valid}')]
#[Description('Print a long-lived play token for local builds and tests (local environment only)')]
class IssueDevPlayToken extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! app()->isLocal()) {
            $this->error('Dev play tokens can only be issued when APP_ENV=local.');

            return self::FAILURE;
        }

        $user = User::query()->where('email', $this->argument('email'))->first();
        $server = Server::query()->where('slug', $this->argument('server'))->first();

        if ($user === null || ! $user->hasVerifiedEmail()) {
            $this->error('No verified user with that email.');

            return self::FAILURE;
        }

        if ($server === null) {
            $this->error('No game server with that slug.');

            return self::FAILURE;
        }

        $issued = PlayToken::issue($user, $server, (int) $this->option('days') * 86400);

        $this->line($issued['token']);

        return self::SUCCESS;
    }
}
