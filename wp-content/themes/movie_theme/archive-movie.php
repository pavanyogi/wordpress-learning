<?php 

get_header();?>

<div class="container container--narrow page-section">
<?php 
while(have_posts()){
    the_post();?>
    <div class="post-item" >
        <h2 class="headline headline--medium headline--post-title" style="text-transform: uppercase;"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
    </div>
    <div class="metabox">
        <p>Posted by <?php the_author_posts_link(); ?> on <?php the_time('n.j.Y') ?></p>
    </div>
    <div>
        <p>Movie Rating : 
            <?php echo get_post_custom_values( 'rating_dropdown' )[0]  ?>
        </p>
    </div>
    <div>
        <p>
        Movie Released On : 
        <?php echo date("Y-m-d",strtotime(get_post_custom_values( 'date_of_release' )[0])) ?>
        </p>
    </div>

    <div>
        <?php the_excerpt(); ?>
        <p><a class="btn btn--blue" href="<?php the_permalink(); ?>">Continue reading &raquo;</a></p>
    </div>
    <hr class="section-break">
<?php }
echo paginate_links();
?>
</div>

<?php get_footer()

?>