<?php

// echo __FILE__;

get_header();?>

<div class="container" style="background-color: #ffd9ff;">
<?php
if(have_posts()) : while(have_posts()) : the_post();?>
    <h1 style="color:#313a47;text-transform: uppercase;text-decoration: underline;">
    <?php
    the_title();?>
    </h1>
    <?php
    echo '<div class="entry-content">';
    the_content();
    echo '</div>';
endwhile; endif;

?>
</div>



