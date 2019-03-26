<?php 
    get_header();

    while (have_posts()) {
        the_post(); ?>
    <div class="container container--narrow page-section">
        <div class="metabox metabox--with-home-link">
          <p><a class="metabox__blog-home-link" style="font-size: 15px;" href="<?php echo get_post_type_archive_link('movie'); ?>"><i class="fa fa-home" aria-hidden="true"></i> Movies Home</a> <span class="metabox__main" style="font-size: 15px;"><?php the_title(); ?></span></p>
        </div>
        <div>
        <?php  
            if ( has_post_thumbnail() ) { // check if the post has a Post Thumbnail assigned to it.
                // the_post_thumbnail( 'full' );
                the_post_thumbnail('medium', ['class' => 'img-responsive ', 'title' => 'Feature image']);
            }

        ?>
        </div>
        <div>
            <p><?php the_content(); ?></p>
        </div>
        
        <p>Posted by <?php the_author_posts_link(); ?> on <?php the_time('n.j.Y') ?></p>
        <?php 
        echo "<br><br>";

        echo '<div><h3>Released On: </h3>'.date("Y-m-d",strtotime(get_post_meta(get_the_ID(), 'date_of_release', TRUE))).'</div>';
        echo '<h3>Rating: </h3>';
        
        $rating = (int)get_post_meta(get_the_ID(), 'rating_dropdown', TRUE);
        for ($i=0; $i < $rating; $i++) { 
            echo '<span class="fa fa-star checked " style="color:orange;"></span>';
        }

        for ($i=0; $i < 5- $rating; $i++) { 
            echo '<span class="fa fa-star"></span>';
        }
        

        $terms = wp_get_post_terms( $post->ID, 'genre');
        $genreMapping  = array(
            'Romantic' => 'romantic',
            'Comedy' => 'comedy',
            'Sci-Fi' => 'scifi', 
        );

        echo '<h3>Genre(s)</h3>';
        echo '<ul>';
        foreach ($terms as $term) {
            echo "<li><a href='http://local.wp-sample5.com/genre/";
            echo $genreMapping[$term->name];
            echo "'>".$term->name."</a></li>";
        }
        echo '</ul>';

        echo '<div class="widget-area">';
        dynamic_sidebar( 'sidebar-11' );
        echo '</div>';
        if (is_single ()) comments_template ();
        ?>
        </div>

    
    <?php }
    get_footer();
?>