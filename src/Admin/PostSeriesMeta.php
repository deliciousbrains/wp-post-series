<?php

namespace DeliciousBrains\WPPostSeries\Admin;

use DeliciousBrains\WPPostSeries\PostSeries;

/**
 * Class to add term meta fields to the post series taxonomy
 */
class PostSeriesMeta {

	const INTRO_PAGE_ID_META_KEY = '_post_series_intro_page_id';
	const INTRO_PAGE_ID_FIELD_NAME = 'introduction';

	public function __construct() {
		if ( is_admin() ) {
			add_action( 'post_series_add_form_fields', [ $this, 'output_create_fields' ], 50, 2 );
			add_action( 'post_series_edit_form_fields', [ $this, 'output_edit_fields' ], 50, 2 );
			add_action( 'created_post_series', [ $this, 'save_fields' ], 10, 1 );
			add_action( 'edited_post_series', [ $this, 'save_fields' ], 10, 1 );
		}
	}

	/**
	 * Show fields for creating a new term in the post series taxonomy.
	 *
	 * @param string $taxonomy The taxonomy we are showing create fields for
	 * @return void
	 */
	public function output_create_fields( $taxonomy ) {
		$this->edit_fields( null, $taxonomy );
	}

	/**
	 * Show fields for editing an existing term in the post series taxonomy.
	 *
	 * @param \WP_Term|null $post_series The post series term we are showing edit fields for
	 * @param string $taxonomy The taxonomy that the term is from
	 * @return void
	 */
	public function output_edit_fields( $post_series, $taxonomy ) {
		$intro_page_id = ! is_null( $post_series ) ? PostSeries::get_intro_page_id( $post_series ) : null;
		?>
		<table
			class="form-table"
			role="presentation">
			<tbody>
			<tr class="form-field">
				<th scope="row">
					<label
						for="<?php echo self::INTRO_PAGE_ID_FIELD_NAME ?>">
						<?php _e( 'Introduction post', PostSeries::TEXT_DOMAIN ) ?>
					</label>
				</th>
				<td>
					<select
						name="<?php echo self::INTRO_PAGE_ID_FIELD_NAME ?>"
						id="<?php echo self::INTRO_PAGE_ID_FIELD_NAME ?>">

						<?php foreach ( get_pages() as $page ) : ?>
							<option
								value="<?php echo esc_attr( $page->ID ) ?>" <?php selected( $page->ID, $intro_page_id ) ?>>
								<?php echo esc_html( get_the_title( $page ) ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<p class="description">
						<?php _e( 'Choose a page to use as the introduction page for the post series.', PostSeries::TEXT_DOMAIN ); ?>
					</p>
				</td>
			</tr>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Save the custom meta fields for post series terms
	 *
	 * @param int $term_id The ID of the term being saved
	 * @return void
	 */
	public function save_fields( $term_id ) {
		if ( isset( $_POST[ self::INTRO_PAGE_ID_FIELD_NAME ] ) ) {
			$sanitized_id = filter_input( INPUT_POST, self::INTRO_PAGE_ID_FIELD_NAME, FILTER_VALIDATE_INT );
			PostSeries::set_intro_page_id( $term_id, $sanitized_id );
		}
	}
}

