<?php

use Phinx\Migration\AbstractMigration;
use App\Model\Image;


class SetStoreImage extends AbstractMigration
{
	public function up()
	{
		$images = $this->query("
			SELECT id, login_id FROM images 
			WHERE login_id IS NULL ORDER BY id
		")->fetchAll();
		
		$this->execute("UPDATE stores SET image_id = {$images[1]['id']}");
	}
	
	public function down()
	{
		// do nothing ...
	}
}
