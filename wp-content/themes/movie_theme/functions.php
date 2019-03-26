<?php

require_once('wp-advanced-search/wpas.php');

function my_search_form() {
    $args = array();
    $args['wp_query'] = array('post_type' => 'post',
                              'posts_per_page' => 5);
    $args['fields'][] = array('type' => 'search',
                              'title' => 'Search',
                              'placeholder' => 'Enter search terms...');
    $args['fields'][] = array('type' => 'taxonomy',
                              'taxonomy' => 'category',
                              'format' => 'select');
    register_wpas_form('my-form', $args);    
}
add_action('init', 'my_search_form');

//funtion to setup js and other required files
function movie_rating_website_files() {
  wp_enqueue_script( 'movie-js', get_theme_file_uri( '/js/scripts-bundled.js' ), null, '1.0', true );
  wp_enqueue_style( 'custom-google-fonts', '//fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:100,300,400,400i,700,700i' );
  wp_enqueue_style( 'font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css' );
  wp_enqueue_style( 'movie_main_style', get_stylesheet_uri() );
  wp_enqueue_style( 'bootstrap-css', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css' );
}
// hook script files
add_action( 'wp_enqueue_scripts', 'movie_rating_website_files' );

//function to create Movie Post Type
function movie_post_types(){
    register_post_type('movie', array(
        'supports' => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', ),
        'taxonomies'  => array( 'genres' ),
        'rewrite' => array('slug' => 'movies'),
        'has_archive' => true,
        'public' => true,
        'show_in_nav_menus' => true,
        'labels' => array(
            'name' => 'Movies',
            'add_new_item' => 'Add New Movie',
            'edit_item' => 'Edit Movie',
            'all_items' => 'All Movies',
            'singular_name' => 'Movie'
        ),
        'menu_icon' => 'dashicons-video-alt2'

    ));
}
// hook to initialize movie post type
add_action('init', 'movie_post_types');

function movies_features()
{
    // add menu
    register_nav_menu('headerMenuLocation','Header Menu Location');
    // add widgets
    register_sidebar(
        array(
            'name' => 'Widgetized Area',
            'id'   => 'sidebar-11',
            'description'   => 'This is a widgetized area.',
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h4>',
            'after_title'   => '</h4>'
        )
    );
    // add image thumbnails
    add_theme_support( 'post-thumbnails' );
    // set width and height for thumbnail
    set_post_thumbnail_size( 800, 600 );
}
add_action('after_setup_theme', 'movies_features');


// function that creates the new metabox that will show on post
function rating_dropdown_metabox() {
    add_meta_box( 
        'rating_dropdown',  // unique id
        __( 'Rating', 'mytheme_textdomain' ),  // metabox title
        'rating_dropdown_display',  // callback to show the dropdown
        'movie'   // post type
    );
}
// action to add meta boxes
add_action( 'add_meta_boxes', 'rating_dropdown_metabox' );


// rating dropdown display
function rating_dropdown_display( $post ) {
    // Use nonce for verification
    wp_nonce_field( basename( __FILE__ ), 'rating_dropdown_nonce' );

    // get current value
    $dropdown_value = get_post_meta( get_the_ID(), 'rating_dropdown', true );
    ?>
    <select name="rating_dropdown" id="rating_dropdown">
        <option value="1" <?php if($dropdown_value == '1') echo 'selected'; ?>>1</option>
        <option value="2" <?php if($dropdown_value == '2') echo 'selected'; ?>>2</option>
        <option value="3" <?php if($dropdown_value == '3') echo 'selected'; ?>>3</option>
        <option value="4" <?php if($dropdown_value == '4') echo 'selected'; ?>>4</option>
        <option value="5" <?php if($dropdown_value == '5') echo 'selected'; ?>>5</option>
    </select>
  <?php
}

// dropdown saving
function rating_dropdown_save( $post_id ) {
    // if doing autosave don't do nothing
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
        return;

    // verify nonce
    if ( !wp_verify_nonce( $_POST['rating_dropdown_nonce'], basename( __FILE__ ) ) )
        return;

    // Check permissions
    if ( 'page' == $_POST['post_type'] ) 
    {
      if ( !current_user_can( 'edit_page', $post_id ) )
          return;
    }
    else
    {
      if ( !current_user_can( 'edit_post', $post_id ) )
          return;
    }

    // save the new value of the dropdown
    $new_value = $_POST['rating_dropdown'];
    update_post_meta( $post_id, 'rating_dropdown', $new_value );
}
// action on saving post
add_action( 'save_post', 'rating_dropdown_save' );


//create a custom taxonomy name it genre for your movies
 
function create_genre_hierarchical_taxonomy() {
 
    // Add new taxonomy, make it hierarchical like categories
    //first do the translations part for GUI

    $labels = array(
      'name' => _x( 'Genre', 'taxonomy general name' ),
      'singular_name' => _x( 'Genre', 'taxonomy singular name' ),
      'search_items' =>  __( 'Search Genres' ),
      'all_items' => __( 'All Genres' ),
      'parent_item' => __( 'Parent Genre' ),
      'parent_item_colon' => __( 'Parent Genre:' ),
      'edit_item' => __( 'Edit Genre' ), 
      'update_item' => __( 'Update Genre' ),
      'add_new_item' => __( 'Add New Genre' ),
      'new_item_name' => __( 'New Genre Name' ),
      'menu_name' => __( 'genre' ),
    );    

    // Now register the taxonomy

    register_taxonomy('genre','movie', array(
      'hierarchical' => true,
      'labels' => $labels,
      'show_ui' => true,
      'show_admin_column' => true,
      'query_var' => true,
      'rewrite' => array( 'slug' => 'genre' ),
    ));
}
add_action( 'init', 'create_genre_hierarchical_taxonomy', 0 );


/* Add CPTs to author archives */
function custom_post_author_archive($query) {
    if ($query->is_author)
        $query->set( 'post_type', array('custom_type', 'movie') );
    remove_action( 'pre_get_posts', 'custom_post_author_archive' );
}
// action to hook pre_get_posts
add_action('pre_get_posts', 'custom_post_author_archive'); 

?>