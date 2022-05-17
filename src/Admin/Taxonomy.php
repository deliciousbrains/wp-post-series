<?php

namespace DeliciousBrains\WPPostSeries\Admin;

use DeliciousBrains\WPPostSeries\PostSeries;

class Taxonomy {

	/**
	 * @var Post
	 */
	protected $post;

	/**
	 * Post constructor.
	 *
	 * @param Post $post
	 */
	public function __construct( Post $post ) {
		$this->post = $post;
	}

	public function init() {
		add_action( 'init', array( $this, 'register_taxonomies' ) );
		add_action( 'init', array( $this, 'register_term_meta' ) );
	}

	public function register_taxonomies() {
		$plural   = __( 'Post series', 'delicious_brains' );
		$singular = __( 'Post series', 'delicious_brains' );

		register_taxonomy( 'post_series', array( 'post' ), array(
			'hierarchical' => false,
			'label'        => $plural,
			'labels'       => array(
				'menu_name'         => __( 'Series', 'delicious_brains' ),
				'name'              => $plural,
				'singular_name'     => $singular,
				'search_items'      => sprintf( __( 'Search %s', 'delicious_brains' ), $plural ),
				'all_items'         => sprintf( __( 'All %s', 'delicious_brains' ), $plural ),
				'parent_item'       => sprintf( __( '%s', 'delicious_brains' ), $singular ),
				'parent_item_colon' => sprintf( __( '%s:', 'delicious_brains' ), $singular ),
				'edit_item'         => sprintf( __( 'Edit %s', 'delicious_brains' ), $singular ),
				'update_item'       => sprintf( __( 'Update %s', 'delicious_brains' ), $singular ),
				'add_new_item'      => sprintf( __( 'Add New %s', 'delicious_brains' ), $singular ),
				'new_item_name'     => sprintf( __( 'New %s Name', 'delicious_brains' ), $singular ),
			),
			'show_ui'      => true,
			'query_var'    => true,
			'rewrite'      => apply_filters( 'wp_post_series_enable_archive', false ),
			'meta_box_cb'  => array( $this->post, 'post_series_meta_box' ),
		) );
	}

    public function register_term_meta() {
        register_term_meta( 'post_series', PostSeriesMeta::INTRO_PAGE_ID_META_KEY, [
            'type' => 'integer',
            'description' => __( 'The ID of the post series introduction page', PostSeries::TEXT_DOMAIN),
            'single' => true,
        ] );
    }
}
