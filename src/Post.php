<?php

namespace DeliciousBrains\WPPostSeries;

class Post {

	public function init() {
		add_filter( 'the_content', array( $this, 'add_series_to_content' ) );
	}

	/**
	 * Append/Prepend the series info box to the post content
	 *
	 * @param  string $content Post content
	 *
	 * @return string Ammended post content
	 */
	public function add_series_to_content( $content ) {
		global $post;

		if ( is_null( $post ) || 'post' !== $post->post_type || ! is_main_query() ) {
			return $content;
		}

		$series = $this->get_post_series( $post->ID );

		if ( ! $series ) {
			return $content;
		}

		// Create series info box
		$term_description = term_description( $series->term_id, 'post_series' );
		$posts_in_series  = get_posts( array(
			'post_type'      => 'post',
			'posts_per_page' => - 1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'orderby'        => 'date',
			'order'          => 'asc',
			'tax_query'      => array(
				array(
					'taxonomy' => 'post_series',
					'field'    => 'slug',
					'terms'    => $series->slug,
				),
			),
		) );

		$post_in_series = 1;

		foreach ( $posts_in_series as $post_id ) {
			if ( $post_id == $post->ID ) {
				break;
			}
			$post_in_series ++;
		}

		// add the series slug to the post series box class
		$post_series_box_class = 'post-series-box series-' . $series->slug;

		if ( is_single() && sizeof( $posts_in_series ) > 1 ) {
			$post_series_box_class .= ' expandable';
		}

		ob_start();

		$this->series_box_html( array(
			'series'                => $series,
			'description'           => $term_description,
			'posts_in_series'       => $posts_in_series,
			'post_in_series'        => $post_in_series,
			'post_series_box_class' => $post_series_box_class,
		) );

		$info_box = ob_get_clean();

		$prepend = apply_filters( 'wp_post_series_prepend_info', true );
		$append  = apply_filters( 'wp_post_series_append_info', true );

		if ( $prepend ) {
			$content = $info_box . $content;
		}

		if ( $append ) {
			$content = $content . $info_box;
		}

		return $content;
	}

	function series_box_html( $args ) {
		extract( $args );
		?>
		<aside class="<?php echo $post_series_box_class; ?>">
			<p class="post-series-name">
				<?php
				if ( apply_filters( 'wp_post_series_enable_archive', false ) ) {
					$series_name = '<a href="' . get_term_link( $series->term_id, 'post_series' ) . '">' . esc_html( $series->name ) . '</a>';
				} else {
					$series_name = esc_html( $series->name );
				}
				printf( __( 'This is article %d of %d in the series <em>&ldquo;%s&rdquo;</em>', PostSeries::TEXT_DOMAIN ), $post_in_series, sizeof( $posts_in_series ), $series_name );
				?>
			</p>

			<?php if ( is_single() && sizeof( $posts_in_series ) > 1 ) : ?>

				<nav class="post-series-nav">
					<ol>
						<?php foreach ( $posts_in_series as $key => $post_id ) : ?>
							<li>
								<?php if ( ! is_single( $post_id ) ) {
									echo '<a href="' . get_permalink( $post_id ) . '">';
								} ?>
								<?php echo get_the_title( $post_id ); ?>
								<?php if ( ! is_single( $post_id ) ) {
									echo '</a>';
								} ?>
							</li>
						<?php endforeach; ?>
					</ol>
				</nav>
			<?php endif; ?>

			<?php if ( is_single() ) : ?>
				<?php if ( $description ) : ?>
					<div class="post-series-description"><?php echo wpautop( wptexturize( $description ) ); ?></div>
				<?php endif; ?>

			<?php endif; ?>
		</aside>
		<?php
	}

	/**
	 * Get a post's series
	 *
	 * @param  int $post_id post ID
	 *
	 * @return object the term object
	 */
	public function get_post_series( $post_id ) {
		$series = wp_get_post_terms( $post_id, 'post_series' );

		if ( ! is_wp_error( $series ) && ! empty( $series ) && is_array( $series ) ) {
			$series = current( $series );
		} else {
			$series = false;
		}

		return $series;
	}

	/**
	 * Get the ID of a post's series
	 *
	 * @param  int $post_id post ID
	 *
	 * @return int series ID
	 */
	public function get_post_series_id( $post_id ) {
		$series = $this->get_post_series( $post_id );

		if ( $series ) {
			return $series->term_id;
		} else {
			return 0;
		}
	}
}
