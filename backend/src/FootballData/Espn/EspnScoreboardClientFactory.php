<?php

namespace Plastonick\Euros\FootballData\Espn;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

final class EspnScoreboardClientFactory
{
    public static function create(string $baseUri): ClientInterface
    {
        return new Client(
            [
                'base_uri' => $baseUri,
                'timeout' => 0,
                'allow_redirects' => false,
            ]
        );
    }
}
