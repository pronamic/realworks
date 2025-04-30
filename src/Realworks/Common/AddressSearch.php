<?php namespace Realworks\Common;

use CustomPost\Search\Form;
use CustomPost\Search\Query;

class AddressSearch
{
	public function apply(Form $form, Query $query)
	{
		if ($address = $form->value('adres'))
		{
			$query->compare('adres', 'like', '%'.str_replace(' ', '%', $address).'%');
		}
	}
}
