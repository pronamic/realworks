<?php namespace CustomPost\Search;

class Rewriter
{
	protected $postType;

	protected $name;

	protected $query;

	public function __construct($postType)
	{
		$this->postType = $postType;
	}

	public function rewrite(array $pages)
	{
		foreach ($pages as $page)
		{
			$this->url($page['url'], $page['query'], array_get($page, 'name'));
		}
	}

	public function url($url, array $query, $name = null)
	{
		global $wp_rewrite;

		$url = trim($url, '/');
		$query = urlencode(json_encode($query));

		$count = preg_match_all('~\{([^:}]+)(?::([^}]+))?\}~', $url, $matches, PREG_SET_ORDER);

		foreach ($matches as $i => $match)
		{
			// Replace URL identifier with regex
			$url = str_replace($match[0], isset($match[2]) ? "({$match[2]})" : '([^/]+)', $url);

			// Replace pattern #{...} with matched values
			$query = str_replace('%23%7B'.$match[1].'%7D', '$matches['.($i + 1).']', $query);
		}

		$redirect = "index.php?post_type={$this->postType}&custompost_query={$query}&custompost_name={$name}";

		add_rewrite_rule("{$wp_rewrite->root}{$url}/?$", $redirect, 'top');
		add_rewrite_rule("{$wp_rewrite->root}{$url}/{$wp_rewrite->pagination_base}/([0-9]{1,})/?$", $redirect.'&paged=$matches['.($count + 1).']', 'top');
	}

	public function enable()
	{
		$GLOBALS['wp']->add_query_var('custompost_query');
		$GLOBALS['wp']->add_query_var('custompost_name');

		add_action('parse_request', array($this, 'parse'));
	}

	public function parse($wp)
	{
		if (isset($wp->query_vars['custompost_query']))
		{
			$query = json_decode(stripslashes($wp->query_vars['custompost_query']), true);

			$this->name = $wp->query_vars['custompost_name'];
			$this->query = $query;

			$_GET = array_merge($_GET, $query);
			$_POST = array_merge($_POST, $query);
			$_REQUEST = array_merge($_REQUEST, $query);
		}
	}

	public function is($name, array $query = array())
	{
		if ($name !== null and $this->name !== $name) return false;

		foreach ($query as $key => $value)
		{
			if (array_get($this->query, $key) !== $value) return false;
		}

		return true;
	}
}
