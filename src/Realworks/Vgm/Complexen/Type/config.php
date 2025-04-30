<?php

return array(
	'post' => array(
		'type' => 'realworks_vgm_type',
		'global' => 'type',
		'register' => array(
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'query_var' => true,
			'rewrite' => array('slug' => 'vgm/type'),
			'capability_type' => 'post',
			'has_archive' => false,
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array('author', 'thumbnail', 'title', 'editor', 'excerpt'),
			'menu_icon' => 'dashicons-redo',
			'labels' => array(
				'name' => 'Types',
				'singular_name' => 'Type',
				'add_new' => 'Toevoegen',
				'add_new_item' => 'Toevoegen',
				'edit_item' => 'Wijzig type',
				'new_item' => 'Nieuw type',
				'view_item' => 'Bekijk type',
				'search_items' => 'Doorzoek types',
				'not_found' => 'Geen types gevonden',
				'not_found_in_trash' => 'Geen types gevonden in prullenbak',
				'parent_item_colon' => 'Type:',
			),
		),
		'wordpress' => array(
			'title' => '#{naam}',
			'content' => '#{aanbiedingstekst}',
			'author' => 1,
			'status' => array(
				'default' => 'publish',
			),
		),
	),
	'search' => array(
		'orderings' => array(),
	),
	'archive' => array(
		'title' => 'VGM',
		'posts_per_page' => 9,
		'pages' => array(),
	),
	'reader' => array(
		'root' => './BouwType',
	),
	'table' => array(
		'name' => 'realworks_vgm_types',
	),
	'paths' => array(
		'template' => 'vgm/type',
		'storage' => 'realworks/vgm/type',
	),
	'unavailable' => '__delete__',
	'auto_delete' => array(
		'max_days' => 180,
		'enabled' => true,
		'days' => 180,
	),
	'media' => true,
	'coordinates' => false,
);
