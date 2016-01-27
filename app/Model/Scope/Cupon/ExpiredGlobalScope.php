<?php 

namespace App\Model\Scope\Cupon;

trait ExpiredGlobalScope
{
	public static function bootExpiredGlobalScope()
	{
		static::addGlobalScope(new ExpiredScope);
	}
	
	/**
	 * Get a new query builder that includes expired cupons.
	 *
	 * @return \Illuminate\Database\Eloquent\Builder|static
	 */
	public static function withExpired()
	{
		return (new static)->newQueryWithoutScope(new ExpiredScope);
	}
}
