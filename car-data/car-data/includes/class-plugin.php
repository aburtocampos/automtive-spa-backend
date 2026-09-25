<?php

namespace AutomotiveInventory;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Plugin {

	private static ?Plugin $instance = null;

	private function __construct() {
		$this->load_dependencies();
		$this->init();
	}

	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function load_dependencies(): void {
		require_once AI_PLUGIN_PATH . 'includes/class-vehicle-post-type.php';
		require_once AI_PLUGIN_PATH . 'includes/class-vehicle-taxonomies.php';
		require_once AI_PLUGIN_PATH . 'includes/class-vehicle-meta.php';
		require_once AI_PLUGIN_PATH . 'includes/class-vehicle-gallery.php';
		require_once AI_PLUGIN_PATH . 'includes/class-vehicle-inquiry.php';
	}

	private function init(): void {
		new Vehicle_Post_Type();
		new Vehicle_Taxonomies();
		new Vehicle_Meta();
		new Vehicle_Gallery();
		new Vehicle_Inquiry();
	}
}