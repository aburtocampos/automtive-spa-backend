<?php

namespace AutomotiveInventory;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Vehicle_Taxonomies {

	public const BRAND        = 'vehicle_brand';
	public const TYPE         = 'vehicle_type';
	public const TRANSMISSION = 'vehicle_transmission';
	public const FUEL_TYPE    = 'vehicle_fuel_type';

	public function __construct() {
		add_action( 'init', [ $this, 'register' ] );
	}

	/**
	 * Register all vehicle taxonomies.
	 */
	public function register(): void {

		$taxonomies = [
			self::BRAND => [
				'singular' => 'Brand',
				'plural'   => 'Brands',
				'rest_base' => 'vehicle-brands',
				'rewrite'  => 'vehicle-brand',
			],

			self::TYPE => [
				'singular' => 'Vehicle Type',
				'plural'   => 'Vehicle Types',
				'rest_base' => 'vehicle-types',
				'rewrite'  => 'vehicle-type',
			],

			self::TRANSMISSION => [
				'singular' => 'Transmission',
				'plural'   => 'Transmissions',
				'rest_base' => 'vehicle-transmissions',
				'rewrite'  => 'vehicle-transmission',
			],

			self::FUEL_TYPE => [
				'singular' => 'Fuel Type',
				'plural'   => 'Fuel Types',
				'rest_base' => 'vehicle-fuel-types',
				'rewrite'  => 'vehicle-fuel-type',
			],
		];

		foreach ( $taxonomies as $taxonomy => $config ) {
			$this->register_taxonomy( $taxonomy, $config );
		}
	}

	/**
	 * Register a vehicle taxonomy.
	 */
	private function register_taxonomy(
		string $taxonomy,
		array $config
	): void {

		$singular = $config['singular'];
		$plural   = $config['plural'];

		$labels = [
			'name'          => __( $plural, 'automotive-inventory' ),
			'singular_name' => __( $singular, 'automotive-inventory' ),
			'search_items'  => sprintf(
				__( 'Search %s', 'automotive-inventory' ),
				$plural
			),
			'all_items'     => sprintf(
				__( 'All %s', 'automotive-inventory' ),
				$plural
			),
			'edit_item'     => sprintf(
				__( 'Edit %s', 'automotive-inventory' ),
				$singular
			),
			'update_item'   => sprintf(
				__( 'Update %s', 'automotive-inventory' ),
				$singular
			),
			'add_new_item'  => sprintf(
				__( 'Add New %s', 'automotive-inventory' ),
				$singular
			),
			'new_item_name' => sprintf(
				__( 'New %s Name', 'automotive-inventory' ),
				$singular
			),
			'menu_name'     => __( $plural, 'automotive-inventory' ),
		];

		register_taxonomy(
			$taxonomy,
			[ Vehicle_Post_Type::POST_TYPE ],
			[
				'labels'            => $labels,
				'public'            => true,
				'hierarchical'      => false,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rest_base'         => $config['rest_base'],
				'rewrite'           => [
					'slug' => $config['rewrite'],
				],
					'meta_box_cb' => false,
			]
		);
	}
}