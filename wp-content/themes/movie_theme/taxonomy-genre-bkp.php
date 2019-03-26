<?php

// echo __FILE__;
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

echo "<div class='container'>";
echo '<div class="row">';

// The Loop
if ( $the_query->have_posts() ) {
    echo '<ul>';
    while ( $the_query->have_posts() ) {
        $the_query->the_post();
        echo '<h1>' . get_the_title() . '</h1>';
        echo '<p><li>' . get_the_content() . '</li></p>';
        echo '<hr>';
    }
    echo '</ul>';
    /* Restore original Post Data */
    wp_reset_postdata();
} else {
    echo '<p>No posts found.</p>';
}

echo '</div>';
echo "</div>";
