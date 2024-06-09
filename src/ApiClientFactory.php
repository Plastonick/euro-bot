<?php

namespace Plastonick\Euros;

use GuzzleHttp\Client;

final class ApiClientFactory
{
    public static function createFootballApiClient(): Client
    {
        return new Client(
            [
                'base_uri' => $_ENV['FOOTBALL_API_URI'],
                'timeout' => 0,
                'allow_redirects' => false,
                'headers' => ['X-Auth-Token' => $_ENV['API_KEY']],
            ]
        );
    }
}
