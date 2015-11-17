<?php

namespace App\Model;

use Illuminate\Database\Query\Expression;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ScopeInterface;

/**
 * Empty class so we can easily spot the column added by the 
 * StoreCoordinatesScope
 */
class StoreCoordinatesExpression extends Expression 
{
}

class StoreCoordinatesScope implements ScopeInterface
{
	public function apply(Builder $builder)
	{
		$builder->select('*')
			->addSelect(new StoreCoordinatesExpression(
				"ST_asText(location) AS coordinates"
			));
	}
	
	public function remove(Builder $builder)
	{
		foreach ($builder->columns as $idx => $column)
		{
			if ($column instanceof StoreCoordinatesExpression)
			{
				unset($builder->columns[$idx]);
			}
		}
	}
}

trait StoreCoordinatesTrait
{
	public static function bootStoreCoordinatesTrait()
	{
		static::addGlobalScope(new StoreCoordinatesScope);
	}
}

class Store extends AbstractBase
{
	use StoreCoordinatesTrait;
	
	
	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = ['login_id', 'location', 'created_at', 'updated_at', 'deleted_at'];
	
	protected $with = ['storetype'];
	
	/** 
	 * Decorator for coordinates.
	 * 
	 * Parses POINT(lat lon) into an array, eg:
	 * 
	 *     ['lat' => -33.000000, 'lon' => -73.000000]
	 * 
	 * @author Phillip Whelan <pwhelan@mixxx.org>
	 */
	public function getCoordinatesAttribute($value)
	{
		return array_combine(
			['lat', 'lon'],
			explode(' ', substr($value, 6, -1))
		);
	}
	
	public function getHoursAttribute($value)
	{
		return json_decode($value);
	}
	
	public function setHours($value)
	{
		$this->attributes['hours'] = json_encode($value);
	}
	
	public function image()
	{
		return $this->belongsTo('App\Model\Image');
	}
	
	public function storetype()
	{
		return $this->belongsTo('App\Model\Storetype');
	}
}
