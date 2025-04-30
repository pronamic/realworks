<?php namespace CustomPost\Database;

use DateTime;
use RuntimeException;
use WPDB as WPDatabase;
use CustomPost\Contracts\DatabaseInterface;

class WordpressDatabase implements DatabaseInterface
{
	protected $db;

	public function __construct(WPDatabase $db)
	{
		$this->db = $db;
	}

	public function insert($table, array $data)
	{
		$fields = array_keys($data);
		$values = array();
		$bindings = array();

		foreach ($data as $field => $value)
		{
			if ($value === null)
			{
				$values[] = 'NULL';
			}
			else
			{
				$values[] = '%s';
				$bindings[] = $this->formatValue($value);
			}
		}

		$sql = "INSERT INTO `{$table}` (`" . implode('`, `', $fields) . '`) VALUES (' . implode(', ', $values) . ')';

		return $this->query($sql, $bindings);
	}

	public function lastInsertId()
	{
		return $this->db->insert_id;
	}

	public function update($table, array $data, array $where)
	{
		$formats = array();
		$wheres = array();
		$bindings = array();

		foreach ($data as $field => $value)
		{
			if ($value === null)
			{
				$formats[] = "`{$field}` = NULL";
			}
			else
			{
				$formats[] = "`{$field}` = %s";
				$bindings[] = $this->formatValue($value);
			}
		}

		foreach ($where as $field => $value)
		{
			$wheres[] = "`{$field}` = %s";
			$bindings[] = $this->formatValue($value);
		}

		$sql = "UPDATE `{$table}` SET " . implode(', ', $formats) . ' WHERE ' . implode(' AND ', $wheres);

		return $this->query($sql, $bindings);
	}

	public function query($query, $data = array())
	{
		if ( ! is_array($data))
		{
			$data = func_get_args();

			array_shift($data);
		}

		return $this->handleError(
			$this->db->query($this->prepare($query, $data))
		);
	}

	public function first($query, $data = array())
	{
		if ( ! is_array($data))
		{
			$data = func_get_args();

			array_shift($data);
		}

		return $this->handleError(
			$this->db->get_row($this->prepare($query, $data), ARRAY_A)
		);
	}

	public function all($query, $data = array())
	{
		if ( ! is_array($data))
		{
			$data = func_get_args();

			array_shift($data);
		}

		return $this->handleError(
			$this->db->get_results($this->prepare($query, $data), ARRAY_A)
		);
	}

	public function column($query, $offset = 0, $data = array())
	{
		if ( ! is_array($data))
		{
			$data = func_get_args();

			array_shift($data);
			array_shift($data);
		}

		return $this->handleError(
			$this->db->get_col($this->prepare($query, $data), $offset)
		);
	}

	public function prepare($query, array $data)
	{
		if (count($data) > 0)
		{
			return $this->db->prepare($query, $data);
		}
		else
		{
			return $query;
		}
	}

	public function cached($method)
	{
		$parameters = func_get_args();

		$key = sha1(serialize(array_map(function($param)
		{
			return function_exists('remove_placeholder_escape') ? remove_placeholder_escape($param) : $param;
		}, $parameters)));

		$result = wp_cache_get($key, 'queries');

		if ($result === false)
		{
			array_shift($parameters);

			$result = call_user_func_array(array($this, $method), $parameters);

			wp_cache_set($key, $result, 'queries', 12 * HOUR_IN_SECONDS);
		}

		return $result;
	}

	protected function formatValue($value)
	{
		if (is_float($value))
		{
			return sprintf('%F', $value);
		}
		else if ($value instanceof DateTime)
		{
			return $value->format('Y-m-d H:i:s');
		}
		else
		{
			return $value;
		}
	}

	protected function handleError($result)
	{
		if ($this->db->last_error)
		{
			throw new RuntimeException("Database error: {$this->db->last_error}\n{$this->db->last_query}");
		}

		return $result;
	}

	public function getTablePrefix()
	{
		return $this->db->prefix;
	}

	public function getTableName($table)
	{
		return $this->db->{$table};
	}
}
