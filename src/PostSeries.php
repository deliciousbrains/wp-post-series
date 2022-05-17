<?php

namespace DeliciousBrains\WPPostSeries;

use DeliciousBrains\WPPostSeries\Admin\PostSeriesMeta;

class PostSeries {

    const TEXT_DOMAIN = 'delicious_brains';

	public function init() {
		$post      = new Post();
		$adminPost = new Admin\Post( $post );
		( new Admin\Taxonomy( $adminPost ) )->init();
		$adminPost->init();
		$post->init();
        $postSeriesMeta = new Admin\PostSeriesMeta();
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
	 * @return int[]|\WP_Post[]
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

    /**
     * Get the ID of the introduction page associated with a post series.
     *
     * Returns the ID if there is a page, or an empty string for any other condition.
     *
     * @param int|\WP_Term $post_series The post series to get the intro page ID for - can be a WP_Term object or an ID
     * @return string
     */
    public static function get_intro_page_id( $post_series ) {
        $term_id = is_a( $post_series, \WP_Term::class ) ? $post_series->term_id : $post_series;

        $page_id = get_term_meta( $term_id, PostSeriesMeta::INTRO_PAGE_ID_META_KEY, true );

        return ( false === $page_id ) ? '' : $page_id;
    }

    /**
     * Get the WP_Post object of the introduction page associated with a post series.
     *
     * @param int|\WP_Term $post_series The post series to get the intro page for - can be a WP_Term object or an ID
     * @return \WP_Post|null
     */
    public static function get_intro_page( $post_series ) {
        $term_id = is_a( $post_series, \WP_Term::class ) ? $post_series->term_id : $post_series;

        $intro_page_id = self::get_intro_page_id();

        if ( empty( $intro_page_id ) ) {
            return null;
        }

        return get_post( $intro_page_id );
    }

    /**
     * Set the ID of the introduction page associated with a post series.
     *
     * @param int|\WP_Term $post_series    The post series to set the intro page ID for - can be a WP_Term object or an ID
     * @param int          $intro_page_id  The page ID value to set
     * @return void
     */
    public static function set_intro_page_id( $post_series, $intro_page_id ) {
        $term_id = is_a( $post_series, \WP_Term::class ) ? $post_series->term_id : $post_series;

        update_term_meta( $term_id, PostSeriesMeta::INTRO_PAGE_ID_META_KEY, $intro_page_id );
    }
}
