<?php

use Phinx\Migration\AbstractMigration;

class AddStoreLocationGistIndex extends AbstractMigration
{
	public function up()
	{
		$this->execute(
			'CREATE INDEX idx_stores_location '.
			'ON stores USING GIST (location)'
		);
	}
	
	public function down()
	{
		$this->execute('DROP INDEX idx_stores_location');
	}
}
