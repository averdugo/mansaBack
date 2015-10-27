<?php

use Phinx\Migration\AbstractMigration;

class CreateTableImages extends AbstractMigration
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
                $this->table('images')
                        ->addColumn('login_id', 'integer')
                        ->addColumn('mimetype', 'string')
                        ->addColumn('data', 'text')
                        ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
                        ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
                        ->addColumn('deleted_at', 'datetime', ['null' => true])
                        ->addForeignKey('login_id', 'logins', 'id',
                                ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
                ->create();
        }
}
