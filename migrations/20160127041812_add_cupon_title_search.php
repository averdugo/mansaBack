<?php

use Phinx\Migration\AbstractMigration;

class AddCuponTitleSearch extends AbstractMigration
{
	public function up()
	{
		$this->execute('DROP INDEX idx_cupons_fts');
		$this->execute(
			"CREATE INDEX idx_cupons_fts ".
			"ON cupons USING gin(to_tsvector('english', title || ' ' || description))"
		);
	}
	
	public function down()
	{
		$this->execute('DROP INDEX idx_cupons_fts');
		$this->execute(
			"CREATE INDEX idx_cupons_fts ".
			"ON cupons USING gin(to_tsvector('english', description))"
		);
	}
}
