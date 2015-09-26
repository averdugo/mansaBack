<?php

namespace App\Model;

class Cupon extends AbstractBase
{
	public function store()
	{
		return $this->belongsTo('App\Model\Store');
	}
}
