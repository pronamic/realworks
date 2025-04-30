<?php

return array(
	'post' => array(
		'type' => 'realworks_vgm_unit',
		'global' => 'woning',
		'register' => array(
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'query_var' => true,
			'rewrite' => array('slug' => 'woning'),
			'capability_type' => 'post',
			'has_archive' => 'vgm-units',
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array('author', 'thumbnail', 'title', 'editor', 'excerpt'),
			'menu_icon' => 'dashicons-admin-home',
			'labels' => array(
				'name' => 'VGM Units',
				'singular_name' => 'VGM Unit',
				'add_new' => 'Toevoegen',
				'add_new_item' => 'Toevoegen',
				'edit_item' => 'Wijzig woning',
				'new_item' => 'Nieuwe woning',
				'view_item' => 'Bekijk woning',
				'search_items' => 'Doorzoek woningen',
				'not_found' => 'Geen woningen gevonden',
				'not_found_in_trash' => 'Geen woningen gevonden in prullenbak',
				'parent_item_colon' => 'Woning:',
			),
		),
		'wordpress' => array(
			'title' => '#{plaats} – #{adres}',
			'content' => '#{aanbiedingstekst}',
			'author' => 1,
			'status' => array(
				'default' => 'publish',
				'fields' => array(
					array(
						'name' => 'status',
						'cases' => array(),
					),
				),
			),
		),
	),
	'search' => array(
		'orderings' => array(),
	),
	'archive' => array(
		'title' => 'VGM Units',
		'posts_per_page' => 9,
		'pages' => array(),
	),
	'reader' => array(
		'root' => './Object',
	),
	'table' => array(
		'name' => 'realworks_vgm_units',
	),
	'paths' => array(
		'template' => 'vgm/units',
		'storage' => 'realworks/vgm/units',
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
