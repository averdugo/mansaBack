<?php

use Phinx\Migration\AbstractMigration;

class CreateTableStores extends AbstractMigration
{
        /**
         * Change Method.
         *
         * Write your reversible migrations using this method.
         *
         * More information on writing migrations is available here:
         * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
         *
         * The following commands can be used in this method and Phinx will
         * automatically reverse them when rolling back:
         *
         *    createTable
         *    renameTable
         *    addColumn
         *    renameColumn
         *    addIndex
         *    addForeignKey
         *
         * Remember to call "create()" or "update()" and NOT "save()" when working
         * with the Table class.
         */
        public function change()
        {
                $this->table('stores')
                        ->addColumn('login_id', 'integer')
                        ->addColumn('img_logo', 'string', ['limit' => 45])
                        ->addColumn('address', 'string', ['limit' => 45])
                        // TODO: pasar a tablas de referencia
                        ->addColumn('comuna', 'string', ['limit' => 45])
                        // TODO: pasar a tablas de referencia
                        ->addColumn('region', 'string', ['limit' => 45])
                        ->addColumn('created_at', 'datetime')
                        ->addColumn('updated_at', 'datetime')
                        ->addColumn('deleted_at', 'datetime')
                        ->addForeignKey('login_id', 'logins', 'id',
                                ['delete'=> 'NO_ACTION', 'update'=> 'NO_ACTION'])
                ->create();
        }
}
