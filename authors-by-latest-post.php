<?php
/**
 * @package Authors_By_Latest_Post
 * @version 0.1
 */
/*
Plugin Name: Authors By Latest Post
Version: 0.1
Description: Displays authors
Author: Mayo Moriyama
*/
class Authors_By_Latest_Post {

	private $source_count = 0;

  /**
   * Constructor.
   */
  function __construct() {

		require_once plugin_dir_path( __FILE__ ) . 'classes/class.rest-api.php';
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		add_shortcode( 'authors'    , array( 'Authors_By_Latest_Post', 'display_authors' ) );

	}
	function plugins_loaded() {

		require_once plugin_dir_path( __FILE__ ) . 'classes/class.update.php';

		add_filter( 'script_loader_tag',      array( $this, 'add_riot_to_script'    ), 10, 2 );

	}

	function add_riot_to_script( $tag, $handle ) {
		if ( 'riot_tag' !== $handle ) {
		    return $tag;
		}
		return preg_replace("/type='text\/javascript'/", 'type="riot/tag"', $tag, 1 );
	}

	static function display_authors( $order = 'ASC' ) {

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'less',     plugin_dir_url( __FILE__ ).'/js/less.min.js',          array(), false, true );
    wp_enqueue_script( 'riot',     plugin_dir_url( __FILE__ ).'/js/riot+compiler.min.js', array(), false, true );
		wp_enqueue_script( 'riot_tag', plugin_dir_url( __FILE__ ).'/js/riot_tag.js',          array( 'jquery', 'less', 'riot' ), date('Y-m-d-His') );

		$script = sprintf( 'var resource_url = "%s";', home_url() );
		$script = $script . 'riot.mount( "div#page-1", "cards" )';
		wp_add_inline_script( 'riot_tag', $script );

		return '<div id="pages"><div id="page-1" count="1" data="{ data }"></div></div>';

	}
	/**
	 * Get post list.
	 *
	 * @param string $author_id Author ID
	 * @return Post_Query|WP_Error Post list if available, error otherwise.
	 */
	static function get_post_query( $author_id = 0, $posts_per_page = 0 ) {

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
				'thumbnail' => get_the_post_thumbnail_url( $post->ID, 'medium_large' )
			);
		}
		return $posts;
	}
}
new  Authors_By_Latest_Post;
