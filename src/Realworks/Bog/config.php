<?php

return array(
	'post' => array(
		'type' => 'realworks_bog',
		'global' => 'object',
		'register' => array(
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'query_var' => true,
			'rewrite' => array('slug' => 'bedrijfspand'),
			'capability_type' => 'post',
			'has_archive' => 'bedrijfsaanbod',
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array('author', 'thumbnail', 'title', 'editor', 'excerpt'),
			'menu_icon' => 'dashicons-admin-home',
			'labels' => array(
				'name' => 'Bedrijfspanden',
				'singular_name' => 'Bedrijfspanden',
				'add_new' => 'Toevoegen',
				'add_new_item' => 'Toevoegen',
				'edit_item' => 'Wijzig object',
				'new_item' => 'Nieuw object',
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
		'title' => 'Bedrijfsaanbod',
		'posts_per_page' => 9,
		'pages' => array(),
	),
	'reader' => array(
		'root' => './Object',
	),
	'table' => array(
		'name' => 'realworks_bog',
	),
	'paths' => array(
		'template' => 'bog',
		'storage' => 'realworks/bog',
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
