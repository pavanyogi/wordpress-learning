<?php
// action to add meta boxes
add_action( 'add_meta_boxes', 'rating_dropdown_metabox' );

// action on saving post
add_action( 'save_post', 'rating_dropdown_save' );

// function that creates the new metabox that will show on post
function rating_dropdown_metabox() {
    add_meta_box( 
        'voodoo_dropdown',  // unique id
        __( 'Voodoo Dropdown', 'mytheme_textdomain' ),  // metabox title
        'rating_dropdown_display',  // callback to show the dropdown
        'movie'   // post type
    );
}

// voodoo dropdown display
function rating_dropdown_display( $post ) {

  // Use nonce for verification
  wp_nonce_field( basename( __FILE__ ), 'voodoo_dropdown_nonce' );

  // get current value
  $dropdown_value = get_post_meta( get_the_ID(), 'voodoo_dropdown', true );
  ?>
    <select name="voodoo_dropdown" id="voodoo_dropdown">
        <option value="USA" <?php if($dropdown_value == 'USA') echo 'selected'; ?>>USA</option>
        <option value="Canada" <?php if($dropdown_value == 'Canada') echo 'selected'; ?>>Canada</option>
        <option value="Mexico" <?php if($dropdown_value == 'Mexico') echo 'selected'; ?>>MEXICO</option>
    </select>
  <?php
}

// dropdown saving
function rating_dropdown_save( $post_id ) {

    // if doing autosave don't do nothing
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
      return;

  // verify nonce
  if ( !wp_verify_nonce( $_POST['voodoo_dropdown_nonce'], basename( __FILE__ ) ) )
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
  $new_value = $_POST['voodoo_dropdown'];
  update_post_meta( $post_id, 'voodoo_dropdown', $new_value );
}
?>