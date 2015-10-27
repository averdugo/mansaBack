<?php

use Phinx\Migration\AbstractMigration;

class IndexStoreLowerComuna extends AbstractMigration
{
	public function up()
	{
		$this->execute(
			'CREATE INDEX idx_stores_comuna '.
			'ON stores (LOWER(comuna))'
		);
	}
	
	public function down()
	{
		$this->execute('DROP INDEX idx_stores_comuna');
	}
}
