----------------------------------------------------------------------
functions.php

<?php
function adding_custom_meta_boxes( $post_type, $post ) {
    add_meta_box( 
        'my-meta-box',
        __( 'My Meta Box' ),
        'render_my_meta_box',
        'movie',
        'normal',
        'default'
    );
}
add_action( 'add_meta_boxes', 'adding_custom_meta_boxes', 10, 2 );

// voodoo dropdown display
function render_my_meta_box( $post ) {

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

?>

-----------------------------------------------------------------------