<?php

namespace App\Model;

class Store extends AbstractBase
{
	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = ['login_id', 'location', 'created_at', 'updated_at', 'deleted_at'];
}
