<?php

namespace AutomotiveInventory;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Vehicle_Gallery {

	private const META_KEY = '_vehicle_gallery';

	public function __construct() {
		add_action( 'init', [ $this, 'register_meta' ] );
		add_action( 'add_meta_boxes', [ $this, 'add_meta_box' ] );
		add_action(
			'save_post_' . Vehicle_Post_Type::POST_TYPE,
			[ $this, 'save' ]
		);
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Register gallery metadata.
	 */
	public function register_meta(): void {

		register_post_meta(
			Vehicle_Post_Type::POST_TYPE,
			self::META_KEY,
			[
				'type'         => 'array',
				'single'       => true,
				'show_in_rest' => [
					'schema' => [
						'type'  => 'array',
						'items' => [
							'type' => 'integer',
						],
					],
				],
				'sanitize_callback' => [ $this, 'sanitize_gallery' ],
			]
		);
	}

	/**
	 * Register gallery meta box.
	 */
	public function add_meta_box(): void {

		add_meta_box(
			'automotive_vehicle_gallery',
			__( 'Vehicle Gallery', 'automotive-inventory' ),
			[ $this, 'render_meta_box' ],
			Vehicle_Post_Type::POST_TYPE,
			'normal',
			'default'
		);
	}

	/**
	 * Load Media Library and gallery assets only on Vehicle screens.
	 */
	public function enqueue_assets( string $hook_suffix ): void {

		if ( ! in_array( $hook_suffix, [ 'post.php', 'post-new.php' ], true ) ) {
			return;
		}

		$screen = get_current_screen();

		if (
			! $screen ||
			Vehicle_Post_Type::POST_TYPE !== $screen->post_type
		) {
			return;
		}

		wp_enqueue_media();

		wp_enqueue_style(
			'automotive-vehicle-gallery',
			AI_PLUGIN_URL . 'assets/admin/vehicle-gallery.css',
			[],
			AI_VERSION
		);

		wp_enqueue_script(
			'automotive-vehicle-gallery',
			AI_PLUGIN_URL . 'assets/admin/vehicle-gallery.js',
			[ 'jquery' ],
			AI_VERSION,
			true
		);
	}

	/**
	 * Render gallery interface.
	 */
	public function render_meta_box( \WP_Post $post ): void {

		wp_nonce_field(
			'automotive_save_vehicle_gallery',
			'automotive_vehicle_gallery_nonce'
		);

		$gallery = get_post_meta(
			$post->ID,
			self::META_KEY,
			true
		);

		if ( ! is_array( $gallery ) ) {
			$gallery = [];
		}
		?>

		<div class="automotive-gallery">

			<input
				type="hidden"
				id="vehicle_gallery"
				name="vehicle_gallery"
				value="<?php echo esc_attr( implode( ',', $gallery ) ); ?>"
			>

			<div
				id="vehicle-gallery-preview"
				class="automotive-gallery__preview"
			>
				<?php foreach ( $gallery as $attachment_id ) : ?>

					<?php
					$image = wp_get_attachment_image(
						$attachment_id,
						'thumbnail',
						false,
						[
							'class' => 'automotive-gallery__image',
						]
					);

					if ( ! $image ) {
						continue;
					}
					?>

					<div
						class="automotive-gallery__item"
						data-id="<?php echo esc_attr( $attachment_id ); ?>"
					>
						<?php echo wp_kses_post( $image ); ?>

						<button
							type="button"
							class="automotive-gallery__remove"
							aria-label="<?php esc_attr_e( 'Remove image', 'automotive-inventory' ); ?>"
						>
							&times;
						</button>
					</div>

				<?php endforeach; ?>
			</div>

			<p class="automotive-gallery__actions">

				<button
					type="button"
					id="vehicle-gallery-select"
					class="button button-primary"
				>
					<?php esc_html_e( 'Select Images', 'automotive-inventory' ); ?>
				</button>

				<button
					type="button"
					id="vehicle-gallery-clear"
					class="button"
				>
					<?php esc_html_e( 'Clear Gallery', 'automotive-inventory' ); ?>
				</button>

			</p>

		</div>

		<?php
	}

	/**
	 * Save gallery attachment IDs.
	 */
	public function save( int $post_id ): void {

		if (
			! isset( $_POST['automotive_vehicle_gallery_nonce'] ) ||
			! wp_verify_nonce(
				sanitize_text_field(
					wp_unslash(
						$_POST['automotive_vehicle_gallery_nonce']
					)
				),
				'automotive_save_vehicle_gallery'
			)
		) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( empty( $_POST['vehicle_gallery'] ) ) {
			delete_post_meta(
				$post_id,
				self::META_KEY
			);

			return;
		}

		$raw_value = sanitize_text_field(
			wp_unslash(
				$_POST['vehicle_gallery']
			)
		);

		$gallery = explode( ',', $raw_value );
		$gallery = $this->sanitize_gallery( $gallery );

		if ( empty( $gallery ) ) {
			delete_post_meta(
				$post_id,
				self::META_KEY
			);

			return;
		}

		update_post_meta(
			$post_id,
			self::META_KEY,
			$gallery
		);
	}

	/**
	 * Sanitize gallery attachment IDs.
	 */
	public function sanitize_gallery( $value ): array {

		if ( ! is_array( $value ) ) {
			return [];
		}

		$attachment_ids = array_map(
			'absint',
			$value
		);

		$attachment_ids = array_filter(
			$attachment_ids,
			static function ( int $attachment_id ): bool {
				return 'attachment' === get_post_type( $attachment_id )
					&& wp_attachment_is_image( $attachment_id );
			}
		);

		return array_values(
			array_unique( $attachment_ids )
		);
	}
}