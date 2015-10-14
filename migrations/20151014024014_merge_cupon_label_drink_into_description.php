<?php

use Phinx\Migration\AbstractMigration;

class MergeCuponLabelDrinkIntoDescription extends AbstractMigration
{
	public function up()
	{
		$cupons = $this->table('cupons');
		
		$cupons->addColumn('description', 'string', ['limit' => 256, 'null' => true])
			->save();
		
		$this->execute("UPDATE cupons SET description = drink || ' ' || label");
		
		$cupons->removeColumn('label')
			->removeColumn('drink');
	}
	
	public function down()
	{
		throw new Exception("Irreversible migration.");
	}
}
