<?php

namespace App\Model;

use Illuminate\Database\Eloquent;


class Cupon extends AbstractBase
{
	use Eloquent\SoftDeletingTrait;
	use Scope\Cupon\ExpiredGlobalScope;
	
	
	protected $dates = ['created_at', 'updated_at', 'deleted_at', 'expires_at'];
	protected $appends = ['has_expired'];
	
	
	public function store()
	{
		return $this->belongsTo('App\Model\Store');
	}
	
	public function getHasExpiredAttribute()
	{
		return $this->expires_at && $this->expires_at->lt(\Carbon\Carbon::now());
	}
}
