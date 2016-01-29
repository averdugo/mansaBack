<?php

use Phinx\Migration\AbstractMigration;
use League\Csv\Reader;


class AddComuneTable extends AbstractMigration
{
	// Simple Wrapper around League\Csv\Reader so we can easily digest
	// the format.
	private function opencsv($file, $columns)
	{
		$csv = League\Csv\Reader::createFromPath('assets/regions/'. $file);
		$csv->setDelimiter(';');
		
		$data = $csv->fetchAll();
		// skip the header with the field names
		array_shift($data);
		
		return $data;
	}
	
	public function up()
	{
		// Create the table for the Regions in Chile
		$regions = $this->table('regions', ['id' => false]);
		$regions
			->addColumn('id', 'integer', ['null' => false])
			->addIndex(['id'], ['unique' => true])
			->addColumn('active', 'boolean', ['default' => false])
			->addColumn('name', 'string', ['limit' => 48])
			->create();
		
		// Load up all of the Regions in Chile
		$regions->insert(
			['id', 'name'],
			$this->opencsv('REGION.csv', ['id', 'name'])
		);
		$regions->saveData();
		
		
		// Create the table for the Provinces in Chile
		$provinces = $this->table('provinces', ['id' => false]);
		$provinces
			->addColumn('id', 'integer', ['null' => false])
			->addIndex(['id'], ['unique' => true])
			->addColumn('active', 'boolean', ['default' => false])
			->addColumn('region_id', 'integer', ['null' => false])
			->addColumn('name', 'string', ['limit' => 48])
			->addForeignKey('region_id', 'regions', 'id', 
				['delete'=> 'NO_ACTION', 'update'=> 'NO_ACTION'])
			->create();
		
		// Load up all of the Provinces in Chile
		$provinces->insert(
			['id', 'name', 'region_id'],
			$this->opencsv('PROVINCIA.csv', ['id', 'name'])
		);
		$provinces->saveData();
		
		
		// Create the table for the Comunes (Comunas) in Chile
		$comunes = $this->table('comunes', ['id' => false]);
		$comunes
			->addColumn('id', 'integer', ['null' => false])
			->addIndex(['id'], ['unique' => true])
			->addColumn('active', 'boolean', ['default' => false])
			->addColumn('province_id', 'integer', ['null' => false])
			->addColumn('name', 'string', ['limit' => 128])
			->addForeignKey('province_id', 'provinces', 'id', 
				['delete'=> 'NO_ACTION', 'update'=> 'NO_ACTION'])
			->create();
		
		// Load up all of the Comunes (Comunas) in Chile
		// ... activate all the comunes in Santiago.
		$comunes->insert(
			['id', 'name', 'province_id', 'active'],
			array_map(
				function($comune) {
					$comune[] = $comune[2] == 131 ? 't' : 'f';
					return $comune;
				},
				$this->opencsv('COMUNA.csv', ['id', 'name'])
			)
		);
		$comunes->saveData();
	}
	
	public function down()
	{
		$this->dropTable('comunes');
		$this->dropTable('provinces');
		$this->dropTable('regions');
	}
}
