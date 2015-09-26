<?php

use Phinx\Migration\AbstractMigration;

class CreateTableCupon extends AbstractMigration
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
                $this->table('cupons')
                        ->addColumn('store_id', 'integer')
                        ->addColumn('label', 'string', ['limit' => 48])
                        ->addColumn('drink', 'string', ['limit' => 48])
                        ->addColumn('price', 'integer')
                        ->addColumn('created_at', 'datetime')
                        ->addColumn('updated_at', 'datetime')
                        ->addColumn('deleted_at', 'datetime')
                        ->addForeignKey('store_id', 'stores', 'id',
                                ['delete'=> 'NO_ACTION', 'update'=> 'NO_ACTION'])
                ->save();
        }
}
