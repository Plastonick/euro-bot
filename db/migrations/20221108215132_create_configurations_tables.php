<?php

use Phinx\Db\Adapter\PostgresAdapter;
use Phinx\Migration\AbstractMigration;

final class CreateConfigurationsTables extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('configurations');

        $table->addColumn('webhook_url', PostgresAdapter::PHINX_TYPE_STRING, ['null' => false]);
        $table->addColumn('service', PostgresAdapter::PHINX_TYPE_STRING, ['null' => false]);
        $table->addColumn('team_map', PostgresAdapter::PHINX_TYPE_JSON, ['null' => false]);
        $table->addColumn('win_emoji', PostgresAdapter::PHINX_TYPE_TEXT, ['null' => false]);
        $table->addColumn('score_emoji', PostgresAdapter::PHINX_TYPE_TEXT, ['null' => false]);
        $table->addColumn('draw_emoji', PostgresAdapter::PHINX_TYPE_TEXT, ['null' => false]);
        $table->addColumn('kickoff_emoji', PostgresAdapter::PHINX_TYPE_TEXT, ['null' => false]);
        $table->addColumn('last_updated', PostgresAdapter::PHINX_TYPE_DATETIME, ['null' => false]);

        $table->addIndex(['webhook_url'], ['unique' => true]);
        $table->addIndex(['last_updated']);

        $table->save();
    }
}
