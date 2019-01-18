<?php
/**
 * @package Authors_By_Latest_Post
 * @version 0.1
 */
/*
Plugin Name: Authors By Latest Post
Version: 0.1
Description: Display writer list of your blog in order of their last published date and time.
Author: Mayo Moriyama
*/
class Authors_By_Latest_Post {

	private $source_count = 0;

  /**
   * Constructor.
   */
  function __construct() {

		require_once plugin_dir_path( __FILE__ ) . 'classes/class.rest-api.php';
		add_action( 'plugins_loaded',            array( $this, 'plugins_loaded' ) );
		add_action( 'transition_post_status',    array( $this, 'update_last_published' ), 10, 3 );
		add_shortcode( 'authors_by_latest_post', array( $this, 'display_authors' ) );
		register_activation_hook( __FILE__,      array( $this, 'bulk_update'           ) );

	}
	function plugins_loaded() {

		add_filter( 'script_loader_tag',      array( $this, 'add_riot_to_script'    ), 10, 2 );

	}

	function add_riot_to_script( $tag, $handle ) {
		if ( 'riot_tag' !== $handle ) {
		    return $tag;
		}
		return preg_replace("/type='text\/javascript'/", 'type="riot/tag"', $tag, 1 );
	}

	static function display_authors( $atts ) {

		$atts = shortcode_atts(
			array(
				'per_page'     => get_option( 'posts_per_page' ),
				'infinite'     => false,
				'max_column'   => 2,
				'show_profile' => true,
				'exclude'      => '',
				'default'      => '',
			), $atts, 'authors_by_latest_post'
		);

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'less',     plugin_dir_url( __FILE__ ).'/js/less.min.js',          array(), false, true );
    wp_enqueue_script( 'riot',     plugin_dir_url( __FILE__ ).'/js/riot+compiler.min.js', array(), false, true );
		wp_enqueue_script( 'riot_tag', plugin_dir_url( __FILE__ ).'/js/riot_tag.js',          array( 'jquery', 'less', 'riot' ), date('Y-m-d-His') );

		$paged  = get_query_var( 'paged', 1 );
		$paged  = ( is_numeric( $paged ) && $paged > 0 ) ? $paged : 1;
		$script = sprintf( 'var resource_url = "%s";', home_url() );
		$script = $script . sprintf(
			'riot.mount( "div#author-list-%d", "cards" )',
			$paged );
		wp_add_inline_script( 'riot_tag', $script );

		$output = sprintf(
			'id="author-list-%s" count="%s" per_page="%s" infinite="%s" max_column="%s" exclude="%s"',
			$paged,
			$paged,
			esc_html( $atts['per_page'] ),
			esc_html( $atts['infinite'] ),
			esc_html( $atts['max_column'] ),
			esc_html( $atts['exclude'] )
		);
		$output = '<div ' . $output . '></div>';

		return '<div id="authors-by-latest-post">' . $output . '</div>';

	}
	/**
	 * Get post list.
	 *
	 * @param string $author_id Author ID
	 * @return Post_Query|WP_Error Post list if available, error otherwise.
	 */
	static function get_post_query( $author_id = 0, $posts_per_page = 1 ) {

		if ( $author_id == 0 ) {
			return new WP_Error( 'ap_no_author', __( 'Author not defined.', 'authors-by-latest-post' ) );
		}

		$query = new WP_Query(
			apply_filters(
				'author_posts_args',
				array(
					'author'              => $author_id,
					'posts_per_page'      => $posts_per_page,
					'no_found_rows'       => true,
					'post_status'         => 'publish',
					'ignore_sticky_posts' => true,
				)
			)
		);

		if ( ! $query->have_posts() ) {
			return new WP_Error( 'ap_no_posts', __( 'Author has no post.', 'authors-by-latest-post' ) );
		}

		foreach( $query->posts as $post ){
			$posts[] = array(
				'title'     => $post->post_title,
				'time'      => $post->post_date,
				'published' => human_time_diff( get_the_time( 'U', $post->ID ) ),
				'permalink' => get_the_permalink($post->ID),
				'thumbnail' => get_the_post_thumbnail_url( $post->ID, 'medium' )
			);
		}
		return $posts;
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
	function bulk_update() {

		$wp_user_query = new WP_User_Query( array( 'role__not_in' => 'Subscriber' ) );
		$authors       = $wp_user_query->get_results();

		if ( !empty( $authors ) ) {

			foreach ($authors as $author) :
				$author_id = $author->ID;
				$query     = self::get_post_query( $author_id );

				if ( !empty( $query ) ) {
					foreach ( $query as $recent_post ) {
						if ( $time = $recent_post['time'] ){
							update_user_meta(
								$author_id,
								'last_published',
								$time
							);
						}
					} // endforeach;
				}
				else {
					update_user_meta(
						$author_id,
						'last_published',
						''
					);

				} // endif;

			endforeach;
		}
	}

}
new Authors_By_Latest_Post;
