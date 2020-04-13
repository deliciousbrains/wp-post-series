<?php

namespace DeliciousBrains\WPPostSeries;

class PostSeries {

	public function init() {
		$post      = new Post();
		$adminPost = new Admin\Post( $post );
		( new Admin\Taxonomy( $adminPost ) )->init();
		$adminPost->init();
		$post->init();
	}

	/**
	 * Get the first post for every series.
	 *
	 * @return \WP_Query
	 */
	public static function get_all() {
		$terms = get_terms( array(
			'taxonomy' => 'post_series',
			'fields'   => 'ids',
		) );

		$args = array(
			'post_per_page' => - 1,
			'post_type'     => 'post',
			'post_status'   => 'publish',
			'tax_query'     => array(
				array(
					'taxonomy' => 'post_series',
					'terms'    => $terms,
				),
			),
		);

		add_filter( 'posts_clauses', array( self::class, 'filter_posts_clauses' ) );
		$query = new \WP_Query();
		$query->query( $args );
		remove_filter( 'posts_clauses', array( self::class, 'filter_posts_clauses' ) );

		$query->posts = array_map( function ( $post ) {
			$post->post_title = $post->name;

			return $post;
		}, $query->posts );

		return $query;
	}

	/**
	 * Filter post clauses to ensure required data is
	 * returned to WP_Query.
	 *
	 * @param array $clauses
	 *
	 * @return array
	 */
	public static function filter_posts_clauses( $clauses ) {
		global $wpdb;

		$clauses['fields']  .= ", {$wpdb->terms}.*";
		$clauses['join']    .= " LEFT JOIN {$wpdb->term_taxonomy} ON ({$wpdb->term_relationships}.term_taxonomy_id = {$wpdb->term_taxonomy}.term_taxonomy_id)";
		$clauses['join']    .= " LEFT JOIN {$wpdb->terms} ON ({$wpdb->term_taxonomy}.term_id = {$wpdb->terms}.term_id)";
		$clauses['groupby'] = "{$wpdb->term_relationships}.term_taxonomy_id";
		$clauses['orderby'] = "{$wpdb->terms}.term_order";

		return $clauses;
	}

	/**
	 * Get all posts from a post series.
	 *
	 * @param int   $term_id Term ID.
	 * @param array $args Query arguments.
	 * @return int[]|WP_Post[]
	 */
	public static function get_all_by_term( $term_id, $args = array() ) {
		$defaults = array(
			'post_type'      => 'post',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'orderby'        => 'date',
			'order'          => 'asc',
			'tax_query'      => array(
				array(
					'taxonomy' => 'post_series',
					'field'    => 'id',
					'terms'    => $term_id,
				),
			),
		);

		return get_posts( wp_parse_args( $args, $defaults ) );
	}
}
