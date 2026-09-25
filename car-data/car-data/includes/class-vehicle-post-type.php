<?php

namespace AutomotiveInventory;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Vehicle_Post_Type {

	public const POST_TYPE = 'vehicle';

	public function __construct() {
		add_action( 'init', [ $this, 'register' ] );
	}

	public function register(): void {

		$labels = [
			'name'               => __( 'Vehicles', 'automotive-inventory' ),
			'singular_name'      => __( 'Vehicle', 'automotive-inventory' ),
			'menu_name'          => __( 'Vehicles', 'automotive-inventory' ),
			'name_admin_bar'     => __( 'Vehicle', 'automotive-inventory' ),
			'add_new'            => __( 'Add New', 'automotive-inventory' ),
			'add_new_item'       => __( 'Add New Vehicle', 'automotive-inventory' ),
			'edit_item'          => __( 'Edit Vehicle', 'automotive-inventory' ),
			'new_item'           => __( 'New Vehicle', 'automotive-inventory' ),
			'view_item'          => __( 'View Vehicle', 'automotive-inventory' ),
			'search_items'       => __( 'Search Vehicles', 'automotive-inventory' ),
			'not_found'          => __( 'No vehicles found.', 'automotive-inventory' ),
			'not_found_in_trash' => __( 'No vehicles found in Trash.', 'automotive-inventory' ),
		];

		$args = [
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,

			// Required so the CPT is available through WordPress REST API.
			'show_in_rest'       => true,
			'rest_base'          => 'vehicles',

			'has_archive'        => true,
			'rewrite'            => [
				'slug'       => 'vehicles',
				'with_front' => false,
			],

			'menu_icon'          => 'dashicons-car',
			'menu_position'      => 20,

			'supports'           => [
				'title',
				'excerpt',
				'thumbnail',
				'custom-fields',
				'editor', 
			],
		];

		register_post_type( self::POST_TYPE, $args );
	}
}