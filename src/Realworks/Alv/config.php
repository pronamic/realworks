<?php

return array(
	'post' => array(
		'type' => 'realworks_alv',
		'global' => 'object',
		'register' => array(
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'query_var' => true,
			'rewrite' => array('slug' => 'object'),
			'capability_type' => 'post',
			'has_archive' => 'alv',
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array('author', 'thumbnail', 'title', 'editor', 'excerpt'),
			'menu_icon' => 'dashicons-admin-home',
			'labels' => array(
				'name' => 'A&LV',
				'singular_name' => 'A&LV',
				'add_new' => 'Toevoegen',
				'add_new_item' => 'Toevoegen',
				'edit_item' => 'Wijzig object',
				'new_item' => 'Nieuwe object',
				'view_item' => 'Bekijk object',
				'search_items' => 'Doorzoek objecten',
				'not_found' => 'Geen objecten gevonden',
				'not_found_in_trash' => 'Geen objecten gevonden in prullenbak',
				'parent_item_colon' => 'Object:',
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
		'title' => 'A&LV',
		'posts_per_page' => 9,
		'pages' => array(),
	),
	'reader' => array(
		'root' => './Object',
	),
	'table' => array(
		'name' => 'realworks_alv',
	),
	'paths' => array(
		'template' => 'alv',
		'storage' => 'realworks/alv',
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
