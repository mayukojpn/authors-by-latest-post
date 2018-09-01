<?php
/**
 * Authors_By_Latest_Post_Update
 *
 * @package Authors_By_Latest_Post
 * @since   0.1
 */
class Authors_By_Latest_Post_Update {

	/**
	 * __construct
	 */
	public function __construct() {
		add_action( 'transition_post_status', array( $this, 'update_last_published' ), 10, 3 );
		add_action( 'activated_plugin',       array( $this, 'activated_plugin'      ), 10, 2 );
		register_activation_hook( __FILE__,   array( $this, 'bulk_update'           ) );
	}

	/**
	 * Fire a callback only when my-custom-post-type posts are transitioned to 'publish'.
	 *
	 * @param string  $new_status New post status.
	 * @param string  $old_status Old post status.
	 * @param WP_Post $post       Post object.
	 */
	function update_last_published( $new_status, $old_status, $post ) {
		if ( ( 'publish' === $new_status && 'publish' !== $old_status )
			&& 'post' === $post->post_type
		) {
			update_user_meta(
				$post->post_author,
				'last_published',
				$post->post_date
			);
		}
	}
	/**
	 * Log plugin activations and deactivations.
	 *
	 * @param  string $plugin
	 * @param  bool   $network_wide
	 * @return void
	 */
	function activated_plugin( $plugin, $network_wide ) {
		if ( $plugin == 'authors-by-latest-post' ) {
			$this->bulk_update();
		}
	}
	function bulk_update() {

		$wp_user_query = new WP_User_Query( array( 'role__not_in' => 'Subscriber' ) );
		$authors       = $wp_user_query->get_results();

		if ( !empty( $authors ) ) {

			foreach ($authors as $author) :
				$author_id = $author->ID;
				$query     = self::get_post_query( $author_id );

				foreach ( $query->posts as $recent_post ) :

					update_user_meta(
						$author_id,
						'last_published',
						$recent_post->post_date
					);

				endforeach;

			endforeach;
		}
	}
}
new Authors_By_Latest_Post_Update;
