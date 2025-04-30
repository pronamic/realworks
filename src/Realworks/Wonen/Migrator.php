<?php namespace Realworks\Wonen;

class Migrator
{
	public function renameAantalVerdiepingen($migrator)
	{
		$migrator->rename('woning.aantalVerdiepingen', 'aantal');
	}
}
