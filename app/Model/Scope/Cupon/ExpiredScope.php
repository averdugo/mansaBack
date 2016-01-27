<?php 

namespace App\Model\Scope\Cupon;

use Illuminate\Database\Eloquent\ScopeInterface;
use Illuminate\Database\Eloquent\Builder;


class ExpiredScope implements ScopeInterface
{
	public function apply(Builder $builder)
	{
		$builder->whereRaw('expires_at > NOW() OR expires_at IS NULL');
	}
	
	public function remove(Builder $builder)
	{
		//$column = $builder->getModel()->getQualifiedActivatedColumn();
		$query = $builder->getQuery();
		foreach ((array)$query->wheres as $key => $where)
		{
			if (
				$where['type'] == 'raw' && 
				$where['sql'] == 'expires_at > NOW()' && 
				$where['boolean'] == 'and')
			{
				unset($query->wheres[$key]);
				$query->wheres = array_values($query->wheres);
			}
		}
	}
}
