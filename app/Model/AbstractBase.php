<?php 

namespace App\Model;

class AbstractBase extends \Illuminate\Database\Eloquent\Model
{
	protected static $BaseNS = "App\\Model\\";
	
	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
	
	public function getTable()
	{
		$c = get_class($this);
		if (substr($c, 0, strlen(self::$BaseNS)) != self::$BaseNS)
		{
			throw new \Exception("Model is not in the correct namespace");
		}
		
		return snake_case(str_plural(substr($c, strlen(self::$BaseNS))));
	}
}
