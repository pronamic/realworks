<?php namespace CustomPost\Plugin\Geo;

use RuntimeException;
use CustomPost\Search\Query;
use CustomPost\Search\Expression;
use CustomPost\Fields\Types\Virtual;
use CustomPost\Search\ExpressionGroup;
use CustomPost\Search\Expressions\Binary;
use JoostK\Wordpress\Support\CoordinatesFetcher;

class RadiusField extends Virtual
{
	protected static $fetcher;

	protected static $fetcherResolver;

	protected static $defaults = array(
		'field' => 'plaats',
	);

	public static function setCoordinatesFetcherResolver($resolver)
	{
		static::$fetcher = null;
		static::$fetcherResolver = $resolver;
	}

	public function newDefaultFormatter()
	{
		return new RadiusFormatter($this);
	}

	public function perform(Query $query, Expression $expression)
	{
		if ( ! $this->hasDatabaseColumns()) return;

		if ($coordinates = $this->coordinates($query, $expression))
		{
			return $this->apply($query, $expression, $coordinates);
		}
	}

	protected function hasDatabaseColumns()
	{
		return $this->parent->getTable()->hasColumn('latitude') and
		       $this->parent->getTable()->hasColumn('longitude');
	}

	protected function coordinates(Query $query, Expression $expression)
	{
		if ($expression instanceof RadiusExpression)
		{
			return $expression->coordinates();
		}
		elseif ($location = $this->getQueriedLocation($query))
		{
			return $this->coordinatesForLocation($location);
		}
	}

	protected function getQueriedLocation(Query $query)
	{
		$location = head($query->getField($this->get('field')));

		if ($location instanceof Binary)
		{
			return $location->getValue();
		}
	}

	protected function apply(Query $query, Expression $expression, array $coordinates)
	{
		$this->ensureJoin($query, $coordinates, $expression->getValue());

		$query->select($this->computedColumn());

		if ($expression instanceof RadiusExpression)
		{
			return $expression->toSql($this->computedColumn());
		}
		else
		{
			return $this->expressionWithLocation($query, $expression);
		}
	}

	protected function expressionWithLocation(Query $query, Expression $expression)
	{
		$original = head($query->getField($this->get('field')));

		$query->forget($original);

		return new ExpressionGroup('or', array(
			$original,
			Expression::sql(null, $expression->toSql($this->computedColumn())),
		));
	}

	protected function ensureJoin(Query $query, array $coordinates, $radius)
	{
		list($lat, $lng) = array($coordinates['latitude'], $coordinates['longitude']);
		list($latString, $lngString) = array(sprintf('%F', $lat), sprintf('%F', $lng));

		$table = $this->parent->getTable()->getName();
		$key = $this->parent->getTable()->getPrimaryKey();

		$subQuery = $query->getDatabase()->prepare(
			"SELECT `{$key}`,
				(6371 * acos(
					cos(radians({$latString}))
					* cos(radians(latitude))
					* cos(radians(longitude) - radians({$lngString}))
					+ sin(radians({$latString}))
					* sin(radians(latitude))
				)) as radius
			FROM {$table}
			WHERE longitude BETWEEN %f AND %f AND latitude BETWEEN %f AND %f",

			array(
				$lng - $radius / abs(cos($lat / 180 * M_PI) * 111.1),
				$lng + $radius / abs(cos($lat / 180 * M_PI) * 111.1),

				$lat - $radius / 111.1,
				$lat + $radius / 111.1,
			)
		);

		$query->joinSubquery($subQuery, 'radius', $key, $table, $key, 'LEFT');
	}

	protected function coordinatesForLocation($location)
	{
		$coordinates = get_option($key = '_geo_'.md5($location)) ?: null;

		if ($coordinates === null and ! get_transient($failed = "{$key}_f"))
		{
			try
			{
				$coordinates = static::fetcher()->fetch("{$location}, Nederland");

				update_option($key, $coordinates ?: '0');
			}
			catch (RuntimeException $e)
			{
				// Prevent many Google Maps requests when an error occured
				set_transient($failed, true, DAY_IN_SECONDS);
			}
		}

		return $coordinates;
	}

	protected function fetcher()
	{
		return static::$fetcher ?: static::$fetcher = call_user_func(static::$fetcherResolver);
	}

	public function computedColumn()
	{
		return '`radius`.`radius`';
	}
}
