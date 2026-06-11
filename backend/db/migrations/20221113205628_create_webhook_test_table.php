<?php
declare(strict_types=1);

use Phinx\Db\Adapter\PostgresAdapter;
use Phinx\Migration\AbstractMigration;

final class CreateWebhookTestTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('webhook_test');

        $table->addColumn('webhook_url', PostgresAdapter::PHINX_TYPE_STRING, ['null' => false]);
        $table->addColumn('service', PostgresAdapter::PHINX_TYPE_STRING, ['null' => false]);

        $table->addIndex(['webhook_url'], ['unique' => true]);

        $table->save();
    }
}
