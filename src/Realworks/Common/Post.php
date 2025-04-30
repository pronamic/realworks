<?php namespace Realworks\Common;

use WP_Query as WordpressQuery;
use CustomPost\Database\Post as BasePost;

class Post extends BasePost
{
	protected $cache = array('media', 'afbeeldingen', 'fotos', 'plattegronden', 'brochures', 'brochure');

	public function groep($groepen, $args = array())
	{
		return $this->media(wp_parse_args($args, array(
			'meta_query' => array(
				array(
					'key' => 'media_group',
					'value' => (array) $groepen,
					'compare' => 'IN',
				),
			),
		)));
	}

	public function afbeeldingen($args = array())
	{
		return $this->groep(array('Foto', 'HoofdFoto', 'Plattegrond'), $args);
	}

	public function fotos($args = array())
	{
		return $this->groep(array('Foto', 'HoofdFoto'), $args);
	}

	public function plattegronden($args = array())
	{
		return $this->groep('Plattegrond', $args);
	}

	public function brochures($args = array())
	{
		return $this->groep('Brochure', $args);
	}

	public function brochure($args = array())
	{
		return $this->brochures(wp_parse_args($args, array(
			'posts_per_page' => 1,
		)))->first();
	}
}
