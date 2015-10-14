<?php

use Phinx\Migration\AbstractMigration;


class AddCuponsSearchIndex extends AbstractMigration
{
	public function up()
	{
		$this->execute(
			"CREATE INDEX idx_cupons_fts ".
			"ON cupons USING gin(to_tsvector('english', description))"
		);
	}
	
	public function down()
	{
		$this->execute('DROP INDEX idx_cupons_fts');
	}
}
