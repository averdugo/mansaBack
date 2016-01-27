<?php

use Phinx\Migration\AbstractMigration;

class MakeCuponStuckNullable extends AbstractMigration
{
	public function up()
	{
		$this->table('cupons')
			->changeColumn('stock', 'integer', ['null' => true])
			->save();
	}
	
	public function down()
	{
		$this->table('cupons')
			->changeColumn('stock', 'integer', ['null' => false])
			->save();
	}

}
