<?php

namespace Plastonick\Euros;

use DateTimeInterface;
use PDO;
use function array_map;
use function date;
use function json_decode;
use function json_encode;
use const DATE_ATOM;

class ConfigurationService implements ConfigurationServiceInterface
{
    public function __construct(private readonly PDO $connection)
    {
    }

    /**
     * @inheritDoc
     */
    public function retrieveConfigurationsSince(DateTimeInterface $lastUpdated): array
    {
        $query = <<<SQL
SELECT *
FROM configurations
WHERE last_updated >= :updatedSince
SQL;

        $statement = $this->connection->prepare($query);
        $statement->execute(['updatedSince' => $lastUpdated->format(DATE_ATOM)]);

        return array_map(fn(array $data) => $this->buildConfiguration($data), $statement->fetchAll());
    }

    public function retrieveConfiguration(string $webhookUrl): ?Configuration
    {
        $query = <<<SQL
SELECT *
FROM configurations
WHERE webhook_url = :webhookUrl
SQL;

        $statement = $this->connection->prepare($query);
        $statement->execute(['webhookUrl' => $webhookUrl]);

        $data = $statement->fetchAll()[0] ?? null;

        if ($data) {
            return $this->buildConfiguration($data);
        } else {
            return null;
        }
    }

    public function deleteConfiguration(string $webhookUrl): bool
    {
        $query = <<<SQL
DELETE FROM configurations
WHERE webhook_url = :webhookUrl
SQL;

        $statement = $this->connection->prepare($query);
        return $statement->execute(['webhookUrl' => $webhookUrl]);
    }

    public function persistConfiguration(
        string $webhookUrl,
        Service $service,
        array $teamMap,
        Emoji $winEmoji,
        Emoji $scoreEmoji,
        Emoji $drawEmoji,
        Emoji $kickoffEmoji
    ): bool {
        $insertQuery = <<<SQL
INSERT INTO configurations (webhook_url, service, team_map, win_emoji, score_emoji, draw_emoji, kickoff_emoji, last_updated) VALUES (
    :webhookUrl,
    :service,
    :teamMap,
    :winEmoji,
    :scoreEmoji,
    :drawEmoji,
    :kickoffEmoji,      
    :lastUpdated      
) ON CONFLICT (webhook_url) DO UPDATE SET service = :service, team_map = :teamMap, win_emoji = :winEmoji, score_emoji = :scoreEmoji, draw_emoji = :drawEmoji, kickoff_emoji = :kickoffEmoji, last_updated = :lastUpdated
 ;
SQL;
        $insertStatement = $this->connection->prepare($insertQuery);

        return $insertStatement->execute([
            'webhookUrl' => $webhookUrl,
            'service' => $service->value,
            'teamMap' => json_encode($teamMap),
            'winEmoji' => $winEmoji->toString(),
            'scoreEmoji' => $scoreEmoji->toString(),
            'drawEmoji' => $drawEmoji->toString(),
            'kickoffEmoji' => $kickoffEmoji->toString(),
            'lastUpdated' => date(DATE_ATOM),
        ]);
    }

    public function persistTestWebhook(string $url, Service $service): bool
    {
        $insertQuery = <<<SQL
INSERT INTO webhook_test (webhook_url, service) VALUES (:webhookUrl, :service) ON CONFLICT DO NOTHING;
SQL;
        $insertStatement = $this->connection->prepare($insertQuery);

        return $insertStatement->execute([
            'webhookUrl' => $url,
            'service' => $service->value,
        ]);
    }

    public function popTestWebhooks(): array
    {
        $query = <<<SQL
SELECT id, webhook_url, service
FROM webhook_test
SQL;

        $statement = $this->connection->prepare($query);
        $statement->execute();

        $this->connection->exec('DELETE FROM webhook_test');

        return $statement->fetchAll();
    }

    private function buildConfiguration(array $data): Configuration
    {
        return new Configuration(
            $data['webhook_url'],
            Service::from($data['service']),
            json_decode($data['team_map'], true),
            Emoji::createFromString($data['win_emoji']),
            Emoji::createFromString($data['score_emoji']),
            Emoji::createFromString($data['kickoff_emoji']),
            Emoji::createFromString($data['draw_emoji'])
        );
    }
}
