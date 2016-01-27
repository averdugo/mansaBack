<?php

use Phinx\Migration\AbstractMigration;

class AddCuponTitle extends AbstractMigration
{
	public function up()
	{
		$cupons = $this->table('cupons');
		
		$cupons->addColumn('title', 'string', ['limit' => 256, 'null' => true])
			->save();
		
		$this->execute("UPDATE cupons SET title = description");
	}
	
	public function down()
	{
		$this->table('cupons')
			->removeColumn('title')
			->save();
	}
}
