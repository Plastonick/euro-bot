<?php
declare(strict_types=1);

use Phinx\Db\Adapter\PostgresAdapter;
use Phinx\Migration\AbstractMigration;

final class AddMessageConfigurations extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('configurations');

        $table->addColumn('kickoff_template', PostgresAdapter::PHINX_TYPE_TEXT, ['null' => true]);
        $table->addColumn('score_template', PostgresAdapter::PHINX_TYPE_TEXT, ['null' => true]);
        $table->addColumn('disallowed_template', PostgresAdapter::PHINX_TYPE_TEXT, ['null' => true]);
        $table->addColumn('win_template', PostgresAdapter::PHINX_TYPE_TEXT, ['null' => true]);
        $table->addColumn('draw_template', PostgresAdapter::PHINX_TYPE_TEXT, ['null' => true]);

        $table->save();
    }
}
