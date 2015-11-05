<?php

namespace App\Model;

class Storetype extends AbstractBase
{
	// No timestamp columns
	public $timestamps = false;
	
	
	public function store()
	{
		return $this->hasMany('App\Model\Store');
	}
}
