<?php

namespace Plastonick\Euros\FootballData\FootballDataOrg;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

final class FootballDataOrgClientFactory
{
    public static function create(string $baseUri, string $apiKey): ClientInterface
    {
        return new Client(
            [
                'base_uri' => $baseUri,
                'timeout' => 0,
                'allow_redirects' => false,
                'headers' => ['X-Auth-Token' => $apiKey],
            ]
        );
    }
}
