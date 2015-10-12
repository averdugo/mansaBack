<?php

namespace App\Model;

class Redemption extends AbstractBase
{
	public function cupon()
	{
		return $this->belongsTo('App\Model\Cupon');
	}
}
