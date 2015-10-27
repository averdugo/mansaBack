<?php

use Phinx\Migration\AbstractMigration;

class DropStoreImgLogo extends AbstractMigration
{
	public function up()
	{
		$this->table('stores')
			->removeColumn('img_logo')
			->save();
	}
	
	public function down()
	{
		throw new Exception('Unable to rollback');
	}
}
