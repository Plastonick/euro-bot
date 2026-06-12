<?php

namespace Plastonick\Test\Euros;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Plastonick\Euros\FootballData\Espn\EspnMatchMapper;
use Plastonick\Euros\FootballData\Espn\EspnScoreboardProvider;
use Plastonick\Euros\FootballData\Espn\EspnTeamMapper;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\NullLogger;

class EspnScoreboardProviderTest extends TestCase
{
    public function testUsesScoreboardPayloadForTeamsAndMatches(): void
    {
        $client = new class implements ClientInterface {
            public int $requests = 0;

            public function send(RequestInterface $request, array $options = []): ResponseInterface
            {
                return $this->response();
            }

            public function sendAsync(RequestInterface $request, array $options = []): \GuzzleHttp\Promise\PromiseInterface
            {
                throw new \RuntimeException('Not implemented');
            }

            public function request($method, $uri, array $options = []): ResponseInterface
            {
                $this->requests++;

                return $this->response();
            }

            public function requestAsync($method, $uri, array $options = []): \GuzzleHttp\Promise\PromiseInterface
            {
                throw new \RuntimeException('Not implemented');
            }

            public function getConfig($option = null)
            {
                return null;
            }

            private function response(): ResponseInterface
            {
                return new Response(
                    200,
                    [],
                    json_encode(
                        [
                            'events' => [
                                [
                                    'id' => '760415',
                                    'date' => '2026-06-11T19:00Z',
                                    'competitions' => [
                                        [
                                            'startDate' => '2026-06-11T19:00Z',
                                            'status' => [
                                                'type' => [
                                                    'state' => 'pre',
                                                    'completed' => false,
                                                ],
                                            ],
                                            'competitors' => [
                                                [
                                                    'homeAway' => 'home',
                                                    'score' => '0',
                                                    'team' => [
                                                        'id' => '203',
                                                        'displayName' => 'Mexico',
                                                        'abbreviation' => 'MEX',
                                                    ],
                                                ],
                                                [
                                                    'homeAway' => 'away',
                                                    'score' => '0',
                                                    'team' => [
                                                        'id' => '467',
                                                        'displayName' => 'South Africa',
                                                        'abbreviation' => 'RSA',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ]
                    )
                );
            }
        };

        $fixture = new EspnScoreboardProvider(
            $client,
            new EspnTeamMapper(),
            new EspnMatchMapper(new NullLogger())
        );

        $teams = $fixture->getTeams('ignored');
        $matches = $fixture->getMatches('ignored', $teams);

        self::assertCount(2, $teams);
        self::assertCount(1, $matches);
        self::assertSame('SCHEDULED', $matches['760415']->status);
        self::assertSame(2, $client->requests);
    }
}
