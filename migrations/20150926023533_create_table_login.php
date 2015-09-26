<?php

use Phinx\Migration\AbstractMigration;

class CreateTableLogin extends AbstractMigration
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
                $this->table('logins')
                        ->addColumn('fname', 'string', ['limit' => 58])
                        ->addColumn('lname', 'string', ['limit' => 58])
                        ->addColumn('phone', 'string', ['limit' => 42])
                        ->addColumn('password', 'string', ['limit' => 128])
                        ->addColumn('email', 'string', ['limit' => 128])
                        ->addColumn('created_at', 'datetime')
                        ->addColumn('updated_at', 'datetime')
                        ->addColumn('deleted_at', 'datetime')
                ->save();
        }
}
