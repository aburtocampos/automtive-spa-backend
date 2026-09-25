<?php

namespace AutomotiveInventory;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Vehicle_Meta {

	public function __construct() {
		add_action( 'init', [ $this, 'register_meta' ] );
		add_action( 'add_meta_boxes', [ $this, 'add_meta_box' ] );
		add_action(
			'save_post_' . Vehicle_Post_Type::POST_TYPE,
			[ $this, 'save' ]
		);
		add_action(
        	'admin_enqueue_scripts',
        	[ $this, 'enqueue_admin_assets' ]
        );
	}

	/**
	 * Register vehicle metadata.
	 */
	public function register_meta(): void {

		register_post_meta(
			Vehicle_Post_Type::POST_TYPE,
			'_vehicle_price',
			[
				'type'              => 'number',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => [ $this, 'sanitize_price' ],
			]
		);

		register_post_meta(
			Vehicle_Post_Type::POST_TYPE,
			'_vehicle_year',
			[
				'type'              => 'integer',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'absint',
			]
		);
		
		register_post_meta(
        	Vehicle_Post_Type::POST_TYPE,
        	'_vehicle_hover_video',
        	[
        		'type'              => 'integer',
        		'single'            => true,
        		'show_in_rest'      => true,
        		'sanitize_callback' => 'absint',
        	]
        );
		
	}

	/**
	 * Register the Vehicle Details meta box.
	 */
	public function add_meta_box(): void {

		add_meta_box(
			'automotive_vehicle_details',
			__( 'Vehicle Details', 'automotive-inventory' ),
			[ $this, 'render_meta_box' ],
			Vehicle_Post_Type::POST_TYPE,
			'normal',
			'high'
		);
	}

	/**
	 * Render the Vehicle Details interface.
	 */
	public function render_meta_box( \WP_Post $post ): void {

		wp_nonce_field(
			'automotive_save_vehicle_details',
			'automotive_vehicle_nonce'
		);

		$price = get_post_meta(
			$post->ID,
			'_vehicle_price',
			true
		);

		$year = get_post_meta(
			$post->ID,
			'_vehicle_year',
			true
		);
		
		$hover_video_id = (int) get_post_meta(
	$post->ID,
	'_vehicle_hover_video',
	true
);

$hover_video_url = $hover_video_id
	? wp_get_attachment_url( $hover_video_id )
	: '';
		

		?>

		<div class="automotive-vehicle-details">

			<?php
			$this->render_taxonomy_select(
				$post->ID,
				Vehicle_Taxonomies::BRAND,
				'Brand'
			);

			$this->render_taxonomy_select(
				$post->ID,
				Vehicle_Taxonomies::TYPE,
				'Vehicle Type'
			);

			$this->render_taxonomy_select(
				$post->ID,
				Vehicle_Taxonomies::TRANSMISSION,
				'Transmission'
			);

			$this->render_taxonomy_select(
				$post->ID,
				Vehicle_Taxonomies::FUEL_TYPE,
				'Fuel Type'
			);
			?>

			<p>
				<label for="vehicle_price">
					<strong>
						<?php esc_html_e( 'Price', 'automotive-inventory' ); ?>
					</strong>
				</label>
			</p>

			<p>
				<input
					type="number"
					id="vehicle_price"
					name="vehicle_price"
					value="<?php echo esc_attr( $price ); ?>"
					min="0"
					step="0.01"
					class="widefat"
				>
			</p>

			<p>
				<label for="vehicle_year">
					<strong>
						<?php esc_html_e( 'Year', 'automotive-inventory' ); ?>
					</strong>
				</label>
			</p>

			<p>
				<input
					type="number"
					id="vehicle_year"
					name="vehicle_year"
					value="<?php echo esc_attr( $year ); ?>"
					min="1900"
					max="2100"
					class="widefat"
				>
			</p>
			
			<hr>

<p>
	<label>
		<strong>
			<?php esc_html_e( 'Card Hover Video', 'automotive-inventory' ); ?>
		</strong>
	</label>
</p>

<p class="description">
	<?php
	esc_html_e(
		'Video displayed when hovering over the vehicle card.',
		'automotive-inventory'
	);
	?>
</p>

<input
	type="hidden"
	id="vehicle_hover_video"
	name="vehicle_hover_video"
	value="<?php echo esc_attr( $hover_video_id ); ?>"
>

<div id="vehicle_hover_video_preview">

	<?php if ( $hover_video_url ) : ?>

		<video
			src="<?php echo esc_url( $hover_video_url ); ?>"
			controls
			muted
			style="width: 100%; max-width: 500px; margin-bottom: 10px;"
		></video>

	<?php endif; ?>

</div>

<p>
	<button
		type="button"
		class="button"
		id="select_vehicle_hover_video"
	>
		<?php esc_html_e( 'Select Video', 'automotive-inventory' ); ?>
	</button>

	<button
		type="button"
		class="button"
		id="remove_vehicle_hover_video"
		<?php echo $hover_video_id ? '' : 'style="display:none;"'; ?>
	>
		<?php esc_html_e( 'Remove Video', 'automotive-inventory' ); ?>
	</button>
</p>

		</div>

		<?php
	}

	/**
	 * Render a taxonomy as a dynamic select.
	 */
	private function render_taxonomy_select(
		int $post_id,
		string $taxonomy,
		string $label
	): void {

		$terms = get_terms(
			[
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
			]
		);

		if ( is_wp_error( $terms ) ) {
			return;
		}

		$current_terms = wp_get_object_terms(
			$post_id,
			$taxonomy,
			[
				'fields' => 'ids',
			]
		);

		$selected_term = ! empty( $current_terms )
			? (int) $current_terms[0]
			: 0;

		$field_name = 'vehicle_tax_' . $taxonomy;

		?>

		<p>
			<label for="<?php echo esc_attr( $field_name ); ?>">
				<strong><?php echo esc_html( $label ); ?></strong>
			</label>
		</p>

		<p>
			<select
				id="<?php echo esc_attr( $field_name ); ?>"
				name="<?php echo esc_attr( $field_name ); ?>"
				class="widefat"
			>
				<option value="">
					<?php
					printf(
						esc_html__( 'Select %s', 'automotive-inventory' ),
						esc_html( $label )
					);
					?>
				</option>

				<?php foreach ( $terms as $term ) : ?>

					<option
						value="<?php echo esc_attr( $term->term_id ); ?>"
						<?php selected( $selected_term, $term->term_id ); ?>
					>
						<?php echo esc_html( $term->name ); ?>
					</option>

				<?php endforeach; ?>

			</select>
		</p>

		<?php
	}

	/**
	 * Save vehicle data.
	 */
	public function save( int $post_id ): void {

		if (
			! isset( $_POST['automotive_vehicle_nonce'] ) ||
			! wp_verify_nonce(
				sanitize_text_field(
					wp_unslash(
						$_POST['automotive_vehicle_nonce']
					)
				),
				'automotive_save_vehicle_details'
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

		$this->save_field(
			$post_id,
			'_vehicle_price',
			'vehicle_price',
			[ $this, 'sanitize_price' ]
		);

		$this->save_field(
			$post_id,
			'_vehicle_year',
			'vehicle_year',
			'absint'
		);
		
		$this->save_field(
	$post_id,
	'_vehicle_hover_video',
	'vehicle_hover_video',
	'absint'
);

		$this->save_taxonomy(
			$post_id,
			Vehicle_Taxonomies::BRAND
		);

		$this->save_taxonomy(
			$post_id,
			Vehicle_Taxonomies::TYPE
		);

		$this->save_taxonomy(
			$post_id,
			Vehicle_Taxonomies::TRANSMISSION
		);

		$this->save_taxonomy(
			$post_id,
			Vehicle_Taxonomies::FUEL_TYPE
		);
	}

	/**
	 * Save a single taxonomy selection.
	 */
	private function save_taxonomy(
		int $post_id,
		string $taxonomy
	): void {

		$field_name = 'vehicle_tax_' . $taxonomy;

		if ( ! isset( $_POST[ $field_name ] ) ) {
			return;
		}

		$term_id = absint(
			wp_unslash(
				$_POST[ $field_name ]
			)
		);

		if ( 0 === $term_id ) {
			wp_set_object_terms(
				$post_id,
				[],
				$taxonomy
			);

			return;
		}

		$term = get_term(
			$term_id,
			$taxonomy
		);

		if (
			! $term ||
			is_wp_error( $term )
		) {
			return;
		}

		wp_set_object_terms(
			$post_id,
			[ $term_id ],
			$taxonomy
		);
	}

	/**
	 * Save a single meta field.
	 */
	private function save_field(
		int $post_id,
		string $meta_key,
		string $field_name,
		callable $sanitize_callback
	): void {

		if ( ! isset( $_POST[ $field_name ] ) ) {
			return;
		}

		$value = call_user_func(
			$sanitize_callback,
			wp_unslash(
				$_POST[ $field_name ]
			)
		);

		update_post_meta(
			$post_id,
			$meta_key,
			$value
		);
	}

	/**
	 * Sanitize vehicle price.
	 */
	public function sanitize_price( $value ): float {
		return max( 0, (float) $value );
	}
	
/**
 * Load admin assets for vehicle editing.
 */
/**
 * Load admin assets for vehicle editing.
 */
public function enqueue_admin_assets( string $hook ): void {

	if (
		'post.php' !== $hook &&
		'post-new.php' !== $hook
	) {
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

	$script_url = plugin_dir_url(
		dirname( __FILE__ )
	) . 'assets/admin/vehicle-meta.js';

	wp_enqueue_script(
		'automotive-vehicle-meta',
		$script_url,
		[ 'jquery' ],
		'1.0.0',
		true
	);
}	
	
}