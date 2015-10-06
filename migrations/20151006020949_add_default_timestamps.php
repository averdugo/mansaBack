<?php

use Phinx\Migration\AbstractMigration;

class AddDefaultTimestamps extends AbstractMigration
{
	public function up()
	{
		foreach(['logins', 'stores', 'cupons'] as $tblname)
		{
			$table = $this->table($tblname);
			
			
			$dates = array_map(
				function($column) {
					$column->setDefault('NOW()');
					return $column;
				},
				array_filter(
					$table->getColumns(),
					function($column) {
						return in_array(
							$column->getName(), 
							['created_at', 'deleted_at', 'updated_at']
						);
					}
				)
			);
			
			
			foreach ($dates as $date)
			{
				$table->changeColumn($date->getName(), $date);
			}
			
			$table->save();
		}
	}
	
	public function down()
	{
		foreach(['logins', 'stores', 'cupons'] as $tblname)
		{
			$table = $this->table($tblname);
			
			
			$dates = array_map(
				function($column) {
					$column->setDefault(NULL);
					return $column;
				},
				array_filter(
					$table->getColumns(),
					function($column) {
						return in_array(
							$column->getName(), 
							['created_at', 'deleted_at', 'updated_at']
						);
					}
				)
			);
			
			
			foreach ($dates as $date)
			{
				$table->changeColumn($date->getName(), $date);
			}
			
			$table->save();
		}
	}
}
