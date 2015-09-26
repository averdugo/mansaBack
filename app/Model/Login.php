<?php

namespace App\Model;

class Login extends AbstractBase
{
	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = ['password', 'created_at', 'updated_at', 'deleted_at'];
	
	public function stores()
	{
		return $this->hasMany('App\Model\Store');
	}
}
