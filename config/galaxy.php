<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Game site
    |--------------------------------------------------------------------------
    |
    | Where the HTML5 game is served (behind a login gate that asks this site's
    | /play/auth-check), and how long a play token is valid for. The token only
    | has to survive the game's start-up, when it's exchanged for a Nakama
    | session.
    |
    */

    'play_url' => env('GALAXY_PLAY_URL', 'https://play.fig.limited'),

    'play_token_ttl' => (int) env('GALAXY_PLAY_TOKEN_TTL', 120),

];
