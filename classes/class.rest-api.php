<?php
/**
 * Authors_By_Latest_Post_Rest_API
 *
 * @package Authors_By_Latest_Post
 * @since   0.1
 */
class Authors_By_Latest_Post_Rest_API {

	/**
	 * __construct
	 */
	public function __construct() {
		add_action( 'rest_api_init',   array( $this, 'rest_api_init'   ) );
		add_filter( 'rest_user_query', array( $this, 'rest_user_query' ), 10, 2);
	}

	/**
	 * Register fields
	 */
	 function rest_api_init() {
		register_rest_field( 'user', 'last_published',
			array(
				'get_callback'      => array( $this, 'rest_callback_get_last_published' ),
				'update_callback'   => null,
				'schema'            => null,
			)
		);
		register_rest_field( 'user', 'post_count',
			array(
				'get_callback'      => array( $this, 'rest_callback_get_post_count' ),
				'update_callback'   => null,
				'schema'            => null,
			)
		);
		register_rest_field( 'user', 'posts',
			array(
				'get_callback'      => array( $this, 'rest_callback_get_posts' ),
				'update_callback'   => null,
				'schema'            => null,
			)
		);
	}

	function rest_callback_get_field( $user, $field_name ) {
		return get_user_meta( $user[ 'id' ], $field_name, true );
	}
	function rest_callback_get_post_count( $user ){
		return count_user_posts( $user[ 'id' ] );
	}

	function rest_callback_get_last_published( $user ) {
		return get_user_meta( $user[ 'id' ], 'last_published', true );
	}

	function rest_callback_get_posts( $user, $field_name, $request) {
		return Authors_By_Latest_Post::get_post_query( $user[ 'id' ], 3 );
	}


	/**
	 * Update user order
	 */
	function rest_user_query ($args, $query) {

		$args["order"]    = "desc";
		$args["orderby"]  = "meta_value";
		$args["meta_key"] = "last_published";

		return $args;
	}
}
new Authors_By_Latest_Post_Rest_API;
