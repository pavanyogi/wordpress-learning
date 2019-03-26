<?php

/**
 * @package wp-api-plugin
 */

/*
Plugin Name: WP Api Plugin
Plugin URI: http://example.com
Description: This plugin is created for demo of wp rest api.
version: 1.0.0
Author: Pavan Yogi
Author URI: http://example.com
Liscense: GPLv2 or later
Text Domain: wp-api-plugin
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


if(! defined('ABSPATH')){
    die(__FILE__.' - '.__LINE__);
}

/**
* HeroThemes REST API Widget
*/
class REST_API_Widget extends WP_Widget { 

    public function activate()
    {

    }

    public function deactivate()
    {

    }

    public function uninstall()
    {

    }
  //set up widget 
  public function __construct() { 
    $widget_ops = array(  'classname' => 'rest-api-widget',
                'description' => 'A REST API widget that pulls posts from a different website'
    );
    parent::__construct( 'rest_api_widget', 'REST API Widget', $widget_ops );
  }

  /**
  * Outputs the content of the widget
  *
  * @param array $args
  * @param array $instance
  */
  public function widget( $args, $instance ) {
    //change this url to the WP-API endpoint for your site!
    // $response = wp_remote_get( 'https://example.com/wp-json/wp/v2/ht-kb/' );
    $response = wp_remote_get( 'http://localhost:3000/posts' );

    if( is_wp_error( $response ) ) {
      return;
    }

    $posts = json_decode( wp_remote_retrieve_body( $response ) );

    if( empty( $posts ) ) {
      return;
    }

    echo $args['before_widget'];
    if( !empty( $instance['title'] ) ) {
      echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) . $args['after_title'];
    }
    
    //main widget content
    if( !empty( $posts ) ) {
      echo '<ul>';
      foreach( $posts as $post ) {
        echo '<li><a href="' . $post->url. '">' . $post->url . '</a></li>';
        echo '<p>'.$post->description.'</p>';
      }
      echo '</ul>';
      
    }

    echo $args['after_widget'];
  }
 } 

 


if(class_exists('REST_API_Widget'))
{
    $REST_API_WidgetPlugin = new REST_API_Widget();
}

// activation
register_activation_hook(__FILE__, array($REST_API_WidgetPlugin, 'activate'));
// deactivation
register_activation_hook(__FILE__, array($REST_API_WidgetPlugin, 'deactivate'));
//uninstall
register_activation_hook(__FILE__, array($REST_API_WidgetPlugin, 'uninstall'));

add_action( 'widgets_init', function(){ register_widget( 'REST_API_Widget' ); } );