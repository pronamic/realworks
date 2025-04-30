<?php namespace JoostK\Wordpress\Config;

use JoostK\Wordpress\Support\ThemeStorage;

class ConfigStorage extends ThemeStorage
{
	public function save(array $config)
	{
		return $this->store($this->contents($config));
	}

	protected function contents(array $config)
	{
		$config = var_export($config, true);
		$config = str_replace("array (\n", "array(\n", $config);
		$config = str_replace(" => NULL,\n", " => null,\n", $config);
		$config = preg_replace("/=>\s*\n\s*array\($/m", "=> array(", $config);
		$config = preg_replace('/^  |\G  /m', "\t", $config);

		return "<?php\n\nreturn {$config};\n";
	}
}
