<?php

function my_theme_enqueue_styles() {
    // wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );

    $parent_style = 'parent-style'; // This is 'twentyfifteen-style' for the Twenty Fifteen theme.
 
    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style ),
        wp_get_theme()->get('Version')
    );
 
}
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );

function wpb_widgets_init() {
 
    register_sidebar( array(
        'name'          => 'Custom Header Widget Area',
        'id'            => 'custom-header-widget',
        'before_widget' => '<div class="chw-widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<h2 class="chw-title">',
        'after_title'   => '</h2>',
    ) );
 
}
add_action( 'widgets_init', 'wpb_widgets_init' );


// function wpb_widgets_init() {
// register_sidebar( array(
// 'name' => 'Header Widget',
// 'id' => 'header-widget',
// 'before_widget' => '<div class="hw-widget">',
// 'after_widget' => '</div>',
// 'before_title' => '<h2 class="hw-title">',
// 'after_title' => '</h2>',
// ) );

// }
// add_action( 'widgets_init', 'wpb_widgets_init' );

?>