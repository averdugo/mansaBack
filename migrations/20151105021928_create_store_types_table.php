<?php

use Phinx\Migration\AbstractMigration;

class CreateStoreTypesTable extends AbstractMigration
{
	/*
	 * Bar
	 * Pub
	 * Disco
	 * Restobar
	 * Botilleria
	 * FonoCopete
	 * Comida
	 */
	public function up()
	{
		$this->table('storetypes')
			->addColumn('label', 'string', ['limit' => 16])
			->insert(['label'], [
				['Bar'],
				['Pub'],
				['Disco'],
				['Restobar'],
				['Botilleria'],
				['FonoCopete'],
				['Comida']
			])
			->save();
		
		$this->table('stores')
			->addColumn('storetype_id', 'integer', ['null' => TRUE])
			->addForeignKey('storetype_id', 'logins', 'id',
                                ['delete'=> 'NO_ACTION', 'update'=> 'NO_ACTION'])
			->save();
	}
	
	public function down()
	{
		$this->table('stores')->removeColumn('storetype_id');
		$this->table('storetypes')->drop();
	}
}
