<?php

return array(
	'post' => array(
		'type' => 'realworks_nieuwbouw',
		'global' => 'project',
		'register' => array(
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'query_var' => true,
			'rewrite' => array('slug' => 'nieuwbouw/project'),
			'capability_type' => 'post',
			'has_archive' => 'nieuwbouw',
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array('author', 'thumbnail', 'title', 'editor', 'excerpt'),
			'menu_icon' => 'dashicons-admin-home',
			'labels' => array(
				'name' => 'Nieuwbouw',
				'singular_name' => 'Nieuwbouwproject',
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
		'title' => 'Nieuwbouw',
		'posts_per_page' => 9,
		'pages' => array(),
	),
	'reader' => array(
		'root' => './Project',
	),
	'table' => array(
		'name' => 'realworks_nieuwbouw',
	),
	'paths' => array(
		'template' => 'nieuwbouw/project',
		'storage' => 'realworks/nieuwbouw/project',
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
