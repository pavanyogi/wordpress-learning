<?php
/**
 * Plugin Name: wp-so-cron
 * Plugin URI: http://example.com/link1
 * Description: This plugin is for fetching stackoverflow api data using wp cron jobs.
 *
 * @package WP Cron
 * Version: 1.0
 * Author: Pavan Yogi @ link
 * Author URI: http://example.com/link3
 * License: GPL2
 * License URI: https://www.gnu.org./licenses/gpl-2.0.html
 * Text Domain: wp-so-cron
 */

/**
 * Activation hook for wp-so-cron plugin
 */
register_activation_hook( __FILE__, 'wp_cron_activation' );

/**
 * Deactivate hook for wp-so-cron plugin
 */
register_deactivation_hook( __FILE__, 'wp_cron_deactivation' );

/**
 * Activation function for wp-so-cron plugin
 */
function wp_cron_activation() {
	if ( ! wp_next_scheduled( 'so_wp_cron_job' ) ) {
		wp_schedule_event( time(), 'minutes_10', 'so_wp_cron_job' );
	}
	flush_rewrite_rules();
}

/**
 * Deactivation function for wp-so-cron plugin
 */
function wp_cron_deactivation() {
	wp_clear_scheduled_hook( 'so_wp_cron_job' );
	flush_rewrite_rules();
}

/**
 * Add 10 minute interval to wp schedules
 *
 * @param array $interval interval for wp cron job.
 */
function new_time_interval( $interval ) {

	$interval['minutes_10'] = array(
		'interval' => 10 * 60,
		'display' => 'Once in 10 minutes',
	);

	return $interval;
}
add_filter( 'cron_schedules', 'new_time_interval' );

/**
 * Fetch external api
 */
function so_cron_action() {
	$url = 'https://api.stackexchange.com/2.2/questions?order=desc&sort=activity&tagged=php&site=stackoverflow&pagesize=3';

	// retrive the raw data from the url.
	$request = wp_remote_get( $url );

	// retrive the body from $request.
	$body = wp_remote_retrieve_body( $request );

	$data = json_decode( $body, true );

	$posts = array();

	$taxonomy = 'so_tags';

	foreach ( $data['items'] as $item ) {
		// insert new tags.
		foreach ( $item['tags'] as $tag ) {
			$term = term_exists( $tag, $taxonomy );
			// If the taxonomy doesn't exist, then we create it.
			if ( 0 === $term || null === $term ) {
				wp_insert_term(
					$tag,
					$taxonomy,
					array(
						'slug' => str_ireplace( ' ', '-', $tag ),
					)
				);
			}
		}

		// create stdClass Object.
		$post = new stdClass();
		$post->post_author = $item['owner']['user_id'];
		$post->post_title = $item['title'];
		$post->post_name = $item['title'];
		$post->post_date = date( 'Y-m-d H:i:s', $item['creation_date'] );
		$post->post_modified = date( 'Y-m-d H:i:s', $item['last_activity_date'] );
		$post->comment_count = $item['answer_count'];
		$post->post_status = 'publish';
		$post->comment_status = 'open';
		$post->menu_order = 0;
		$post->post_content = '<a href="' . $item['link'] . '">' . $item['title'] . '</a>';
		$post->to_ping = '';
		$post->pinged = '';
		$post->post_date_gmt = date( 'Y-m-d H:i:s', $item['creation_date'] );
		$post->guid = $item['link'];
		$post->ping_status = 'closed';
		$post->post_password = '';
		$post->post_type = 'so_questions';
		$post->filter = 'raw';
		$post->post_mime_type = '';
		$post->post_parent = 0;
		$post->post_content_filtered = '';
		$post->post_excerpt = '';
		$post->post_modified_gmt = date( 'Y-m-d H:i:s', $item['last_activity_date'] );

		// Convert to WP_Post object.
		$wp_post = new WP_Post( $post );

		// Insert the post into the database.
		$post_id = wp_insert_post( $wp_post );

		// set terms with custom post.
		wp_set_object_terms( $post_id, $item['tags'], $taxonomy, true );
	}
}
add_action( 'so_wp_cron_job', 'so_cron_action' );

/**
 * Creating a SO Question Custom Post Type
 */
function so_questions_custom_post_type() {
	$labels = array(
		'name'                => __( 'SO Questions' ),
		'singular_name'       => __( 'SO Question' ),
		'menu_name'           => __( 'SO Questions' ),
		'parent_item_colon'   => __( 'Parent so_question' ),
		'all_items'           => __( 'All SO Questions' ),
		'view_item'           => __( 'View SO Question' ),
		'add_new_item'        => __( 'Add New SO Question' ),
		'add_new'             => __( 'Add New' ),
		'edit_item'           => __( 'Edit SO Question' ),
		'update_item'         => __( 'Update SO Question' ),
		'search_items'        => __( 'Search SO Question' ),
		'not_found'           => __( 'Not Found' ),
		'not_found_in_trash'  => __( 'Not found in Trash' ),
	);
	$args = array(
		'label'               => __( 'SO Questions' ),
		'description'         => __( 'StackOverflow Questions' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'revisions', 'custom-fields' ),
		'public'              => true,
		'hierarchical'        => false,
		'menu_icon' => plugins_url( 'images/so-icon-20x20.png', __FILE__ ),
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'has_archive'         => true,
		'can_export'          => true,
		'exclude_from_search' => false,
		'taxonomies'          => array( 'so_question' ),
		'publicly_queryable'  => true,
		'capability_type'     => 'page',
	);
	register_post_type( 'so_questions', $args );
}
add_action( 'init', 'so_questions_custom_post_type', 0 );

/**
 * Create a custom taxonomy name it "so_tags" for your custom posts
 */
function create_so_tags_custom_taxonomy() {

	$labels = array(
		'name'                => _x( 'SO Tags', 'taxonomy general name' ),
		'singular_name'       => _x( 'SO Tag', 'taxonomy singular name' ),
		'search_items'        => __( 'Search SO Tags' ),
		'all_items'           => __( 'All Tags' ),
		'parent_item'         => __( 'Parent Tag' ),
		'parent_item_colon'   => __( 'Parent Tag:' ),
		'edit_item'           => __( 'Edit Tag' ),
		'update_item'         => __( 'Update Tag' ),
		'add_new_item'        => __( 'Add New Tag' ),
		'new_item_name'       => __( 'New Tag Name' ),
		'menu_name'           => __( 'Tags' ),
	);

	register_taxonomy(
		'so_tags',
		array( 'so_questions' ),
		array(
			'hierarchical'       => true,
			'labels'             => $labels,
			'show_ui'            => true,
			'show_admin_column'  => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'so_tag' ),
		)
	);
}
add_action( 'init', 'create_so_tags_custom_taxonomy', 0 );

/**
 * Modify content of post (insert tags of custom post in content)
 *
 * @param      string $content The content.
 *
 * @return     string  ( tags inserted/appended in the content )
 */
function show_tags_with_single_post_content( $content ) {

	global $post;

	$post_tags = get_the_terms( $post->ID, 'so_tags' );

	foreach ( $post_tags as $value ) {
		$tag_content .= "<a href=' " . get_tag_link( $value->term_id ) . "'> $value->name , </a>";
	}
	$tag_content = rtrim( $tag_content, ', </a>' );
	$tag_content .= '</a>';

	return 'Tags: ' . $tag_content . '<br><br>' . $content;
}
add_filter( 'the_content', 'show_tags_with_single_post_content' );
