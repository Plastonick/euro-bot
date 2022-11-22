<?php
declare(strict_types=1);

use Phinx\Db\Adapter\PostgresAdapter;
use Phinx\Migration\AbstractMigration;

final class AddDelayConfig extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('configurations');

        $table->addColumn('delay_seconds', PostgresAdapter::PHINX_TYPE_INTEGER, ['null' => false, 'default' => 0]);

        $table->save();
    }
}
