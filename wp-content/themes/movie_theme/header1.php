<! DOCTYPE html>
<html <?php language_attributes(); ?> >
    <head>
        <meta chatset="<?php  bloginfo('charset'); ?>" >
        <meta name="viewport" content="width=device-width, initial-scale=1" >
        <?php wp_head(); ?>
    </head>
    <body <?php body_class(); ?> >
        <header class="site-header">
            <div class="container">
              <div class="site-header__menu group">
                <div class="site-header__util">
                  <a href="#" class="btn btn--small btn--orange float-left push-right">Login</a>
                  <a href="#" class="btn btn--small  btn--dark-orange float-left">Sign Up</a>
                  <span class="search-trigger js-search-trigger"><i class="fa fa-search" aria-hidden="true"></i></span>
                </div>
              </div>

              <div class="site-header__menu group">
              </div>

            </div>
        </header>