<?php
/*
Plugin Name: My WP-Cron Test
*/

// echo '<pre>'; print_r( _get_cron_array() ); echo '</pre>';
/*
function my_cron_schedules($schedules){

    if(!isset($schedules["1min"])){
    $schedules["1min"] = array(
        'interval' => 60,
        'display' => __('Once every 1 minute'));
    }

    if(!isset($schedules["5min"])){
        $schedules["5min"] = array(
            'interval' => 5*60,
            'display' => __('Once every 5 minutes'));
    }
    if(!isset($schedules["30min"])){
        $schedules["30min"] = array(
            'interval' => 30*60,
            'display' => __('Once every 30 minutes'));
    }
    return $schedules;
}
add_filter('cron_schedules','my_cron_schedules');

if (!wp_next_scheduled('my_task_hook')) {
    wp_schedule_event( time(), '1min', 'my_task_hook' );
}
add_action ( 'my_task_hook', 'my_task_function' );

function my_task_function() {
    echo 'I have been called to action. I will do the same next week';
    $data = date('Y-m-d H:i:s.') . gettimeofday()['usec'].' - '.__FUNCTION__." - ".__FILE__.' - '.__LINE__.' - debug';
    file_put_contents('/home/pavan/Documents/custom_server/log.txt', $data.PHP_EOL, FILE_APPEND | LOCK_EX);
}
*/

/*
// Add a new interval of 180 seconds
// See http://codex.wordpress.org/Plugin_API/Filter_Reference/cron_schedules
add_filter( 'cron_schedules', 'isa_add_every_one_minutes' );
function isa_add_every_one_minutes( $schedules ) {
    $schedules['every_one_minutes'] = array(
            'interval'  => 60,
            'display'   => __( 'Every 1 Minutes', 'textdomain' )
    );
    return $schedules;
}

// Schedule an action if it's not already scheduled
if ( ! wp_next_scheduled( 'isa_add_every_one_minutes' ) ) {
    wp_schedule_event( time(), 'every_one_minutes', 'isa_add_every_one_minutes' );
}

// Hook into that action that'll fire every one minutes
add_action( 'isa_add_every_one_minutes', 'every_one_minutes_event_func' );
function every_one_minutes_event_func() {
    // do something
    echo 'I have been called to action. I will do the same next minute';
    $data = date('Y-m-d H:i:s.') . gettimeofday()['usec'].' - '.__FUNCTION__." - ".__FILE__.' - '.__LINE__.' - debug';
    file_put_contents('/home/pavan/Documents/custom_server/log.txt', $data.PHP_EOL, FILE_APPEND | LOCK_EX);
}
*/





// add 10 minute interval to wp schedules
function new_interval($interval) {

    $interval['minutes_10'] = array('interval' => 10*60, 'display' => 'Once in 10 minutes');

    return $interval;
}
add_filter('cron_schedules', 'new_interval');

function MyCronAction() {

    $data = date('Y-m-d H:i:s.') . gettimeofday()['usec'].' - '.__FUNCTION__." - ".__FILE__.' - '.__LINE__.' - debug';
    file_put_contents('/home/pavan/Documents/custom_server/log.txt', $data.PHP_EOL, FILE_APPEND | LOCK_EX);

    $url = 'https://api.stackexchange.com/2.2/questions?order=desc&sort=activity&tagged=php&site=stackoverflow';
    //retrive the raw data from the url
    $request = wp_remote_get( $url );
    
    //retrive the body from $request
    $body = wp_remote_retrieve_body( $request );
    
    $data = json_decode( $body , true);

    $posts = array();
    $num = 0;

    foreach( $data["items"] as $item ) {

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
        $post->post_content = '<a href="'.$item["link"].'">'.$item["title"].'</a>';
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
    }
   

}
add_action('new_interval', 'MyCronAction');

register_activation_hook( __FILE__, 'my_activation' );
register_deactivation_hook(__FILE__, 'my_deactivation');

function my_activation() {
    if (!wp_next_scheduled('MyCronEvent')) {
        wp_schedule_event(time(), 'minutes_10', 'new_interval');
    }
}

function my_deactivation() {
    wp_clear_scheduled_hook('new_interval');
    wp_unschedule_event(time(), 'minutes_10', 'new_interval');
}




/*

function my_cron_schedules($schedules){
    if(!isset($schedules["5min"])){
        $schedules["5min"] = array(
            'interval' => 20,
            'display' => __('Once every 5 minutes'));
    }
    if(!isset($schedules["30min"])){
        $schedules["30min"] = array(
            'interval' => 30*60,
            'display' => __('Once every 30 minutes'));
    }
    return $schedules;
}
add_filter('cron_schedules','my_cron_schedules');

$args = array(false);
function schedule_my_cron(){
    wp_schedule_event(time(), '5min', 'my_schedule_hook');
}
if(!wp_next_scheduled('my_schedule_hook',$args)){
    add_action('init', 'schedule_my_cron');
}

function my_schedule_hook(){
    // codes go here
    echo 'I have been called to action. I will do the same next minute';
    $data = date('Y-m-d H:i:s.') . gettimeofday()['usec'].' - '.__FUNCTION__." - ".__FILE__.' - '.__LINE__.' - debug';
    file_put_contents('/home/pavan/Documents/custom_server/log.txt', $data.PHP_EOL, FILE_APPEND | LOCK_EX);
}
*/
