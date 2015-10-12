<?php

use Phinx\Migration\AbstractMigration;

class CreatePostgisExtension extends AbstractMigration
{
	public function up()
	{
		//$this->execute("CREATE EXTENSION postgis");
	}
	
	public function down()
	{
		$this->execute("DROP EXTENSION postgis");
	}
}
