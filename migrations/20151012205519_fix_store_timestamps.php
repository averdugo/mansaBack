<?php

use Phinx\Migration\AbstractMigration;

class FixStoreTimestamps extends AbstractMigration
{
	public function up()
	{
		foreach(['stores'] as $tblname)
		{
			$table = $this->table($tblname);
			
			
			$dates = array_map(
				function($column) {
					if ($column->getName() == 'deleted_at')
					{
						$column->setDefault(NULL);
						$column->setNull(TRUE);
					}
					else
					{
						$column->setDefault('CURRENT_TIMESTAMP');
						$column->setNull(FALSE);
					}
					
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
		foreach(['stores'] as $tblname)
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

