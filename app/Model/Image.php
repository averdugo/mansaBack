<?php

namespace App\Model;

class Image extends AbstractBase
{
	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = ['login_id', 'data', 'created_at', 'updated_at', 'deleted_at'];
}
