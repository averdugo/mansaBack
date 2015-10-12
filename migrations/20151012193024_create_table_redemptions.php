<?php

use Phinx\Migration\AbstractMigration;

class CreateTableRedemptions extends AbstractMigration
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
		$this->table('redemptions')
			->addColumn('cupon_id', 'integer', ['default' => NULL, 'null' => FALSE])
			->addColumn('device_id', 'string', ['limit' => 48])
			->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
			->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
			->addColumn('deleted_at', 'datetime', ['null' => TRUE])
			->addForeignKey('cupon_id', 'cupons', 'id',
				['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
			->addIndex(['cupon_id', 'device_id'], ['unique' => TRUE])
		->create();
	}
}
