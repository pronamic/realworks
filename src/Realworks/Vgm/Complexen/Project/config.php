<?php

return array(
	'post' => array(
		'type' => 'realworks_vgm',
		'global' => 'project',
		'register' => array(
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'query_var' => true,
			'rewrite' => array('slug' => 'vgm/project'),
			'capability_type' => 'post',
			'has_archive' => 'vgm',
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array('author', 'thumbnail', 'title', 'editor', 'excerpt'),
			'menu_icon' => 'dashicons-admin-home',
			'labels' => array(
				'name' => 'VGM',
				'singular_name' => 'VGM project',
				'add_new' => 'Toevoegen',
				'add_new_item' => 'Toevoegen',
				'edit_item' => 'Wijzig project',
				'new_item' => 'Nieuwe project',
				'view_item' => 'Bekijk project',
				'search_items' => 'Doorzoek projecten',
				'not_found' => 'Geen projecten gevonden',
				'not_found_in_trash' => 'Geen projecten gevonden in prullenbak',
				'parent_item_colon' => 'Project:',
			),
		),
		'wordpress' => array(
			'title' => '#{plaats} – #{projectnaam}',
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
		'root' => './Project',
	),
	'table' => array(
		'name' => 'realworks_vgm',
	),
	'paths' => array(
		'template' => 'vgm/project',
		'storage' => 'realworks/vgm/project',
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
