<?php

get_header();

global $wp;
$current_slug = add_query_arg( array(), $wp->request );


if ($current_slug == 'genre/comedy') {
    $genreOfMovie = 'comedy' ;
} elseif ($current_slug == 'genre/scifi') {
    $genreOfMovie = 'scifi' ;
} elseif ($current_slug == 'genre/romantic') {
    $genreOfMovie = 'romantic' ;
}

$args = array(
    'post_type' => 'movie' , 
    'genre' => $genreOfMovie
);

$the_query = new WP_Query( $args );


?>
<head>
</head>
<body>

<?php
echo '<div class="container" style="background-color: #ffd9ff;">';
//// The Loop
if ( $the_query->have_posts() ) {
    while ( $the_query->have_posts() ) {
        $the_query->the_post();
        echo '<div class="row">';
        echo '<div class="col-md-4">';
        echo '<h1 style="color:#313a47;text-transform: uppercase;text-decoration: underline;">' . get_the_title() . '</h1>';
        echo '<p><li>' . get_the_content() . '</li></p>';
        echo '</div>';
    }
    echo '</div';
    echo '</div';

    /* Restore original Post Data */
    wp_reset_postdata();
} else {
    echo '<p>No posts found.</p>';
}
?>


  
    
    
</body>
</div>
</div>
