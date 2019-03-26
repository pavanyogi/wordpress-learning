<?php
/**
 * Call external api and display results
 *
 * @package wp-search-api-plugin
 */

/*
Plugin Name: WP Search Api Plugin
Plugin URI: http://example.com
Description: This plugin is created for demo of wp search api.
version: 1.0.0
Author: Pavan Yogi
Author URI: http://example.com
Liscense: GPLv2 or later
Text Domain: wp-search-api-plugin
 */

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Copyright 2005-2015 Automattic, Inc.
*/


if ( ! defined( 'ABSPATH' ) ) {
	die( __FILE__ . ' - ' . __LINE__ );
}



/**
 * REST API Widget
 */
class REST_API_Widget extends WP_Widget {
	/**
	 * Activate the plugin
	 */
	public function activate() {

	}
	/**
	 * Deactivte the plugin
	 */
	public function deactivate() {

	}
	/**
	 * Uninstall the plugin
	 */
	public function uninstall() {

	}

	// set up

	/**
	 * Constructor
	 */
	public function __construct() {
        add_filter('the_posts', array( $this, 'search_posts' ),10,1);
        add_filter( 'the_permalink', array( $this, 'change_permalink_to_guid' ), 999, 2 );
        add_filter( 'post_link', array( $this, 'change_permalink_to_guid' ), 999, 2 );
	}

    public function search_posts( $posts ) {

        if ( is_search() ) {

            $url = 'https://api.stackexchange.com/2.2/questions?order=desc&sort=activity&tagged='.$_GET['s'].'&site=stackoverflow';
            //retrive the raw data from the url
            $request = wp_remote_get( $url );
            
            //retrive the body from $request
            $body = wp_remote_retrieve_body( $request );
            
            $data = json_decode( $body , true);

            $posts = array();
            $num = 0;

            $tmpLoopCount = 0;
            foreach( $data["items"] as $item ) {

                if ($tmpLoopCount == 4) {
                    break;
                }
                $tmpLoopCount++;

                //create stdClass Object
                $post = new stdClass();
                // $post->ID = $item["question_id"];
                $post->post_author = $item["owner"]["user_id"];
                $post->post_title = $item["title"];
                $post->post_name = $item["title"];
                $post->post_date = date( 'Y-m-d H:i:s', $item["creation_date"] );
                $post->post_modified = date( 'Y-m-d H:i:s', $item["last_activity_date"] );
                $post->comment_count = $item["answer_count"];
                $post->post_status = 'publish';
                $post->comment_status = 'open';
                $post->menu_order = 0;
                $post->post_content = '';
                $post->to_ping = '';
                $post->pinged = '';
                $post->post_date_gmt = date( 'Y-m-d H:i:s', $item["creation_date"] );
                $post->guid = $item["link"];
                $post->ping_status = 'closed';
                $post->post_password = '';
                $post->post_type = 'post';
                $post->filter = 'raw';
                $post->post_mime_type = '';
                $post->post_parent = 0;
                $post->post_content_filtered = '';
                $post->post_excerpt = '';
                $post->post_modified_gmt =  date( 'Y-m-d H:i:s', $item["last_activity_date"] );

                // Convert to WP_Post object
                $wp_post = new WP_Post( $post );

                // Insert the post into the database
                wp_insert_post( $wp_post );

                //added to posts array
                $posts[$num] = $wp_post;

                $num++;

            }
           
        }
        return $posts;

    }

    public function change_permalink_to_guid( $permalink, $postID ) {

        if ( is_search() ) {

            //get the Guid
            $external_link = get_the_guid( $postID );
            if( !empty( $external_link ) ) {
                //changing the permalink for guid
                $permalink = esc_url( $external_link );
            }
        }
        
        return $permalink;
    }
}




if ( class_exists( 'REST_API_Widget' ) ) {
	$rest_api_widget_plugin = new REST_API_Widget();
}

// Activation.
register_activation_hook( __FILE__, array( $rest_api_widget_plugin, 'activate' ) );
// Deactivation.
register_activation_hook( __FILE__, array( $rest_api_widget_plugin, 'deactivate' ) );
// Uninstall.
register_activation_hook( __FILE__, array( $rest_api_widget_plugin, 'uninstall' ) );

add_action(
	'widgets_init',
	function() {
		register_widget( 'REST_API_Widget' );
	}
);




