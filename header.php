<?php

/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package lemonjelly
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;
$is_transparent_navbar = get_field('transparent_navbar', 'option');

$is_standard_navbar = get_field('standard_navbar', 'option');

if ($is_standard_navbar) {
  $body_class = 'has-standard-navbar';
} else {
  $body_class = '';
}



?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <link rel="profile" href="https://gmpg.org/xfn/11">
  <?php wp_head(); ?>
</head>

<body <?php body_class($body_class); ?>>
  <?php wp_body_open(); ?>
  <div id="page" class="site">

    <a class="skip-link screen-reader-text" href="#content"><?php esc_html_e('Skip to content', 'lemonjelly'); ?></a>

    <header id="masthead" class="site-header">
      <nav id="site-navigation" class="navbar main-navigation">
        <div class="container">
          <div class="navbar-brand site-branding">

            <?php
            $logo_id = get_field('main_logo', 'option');
            if ($logo_id): ?>
              <figure class="site-logo navbar-item">
                <a href="<?php echo esc_url(home_url('/')); ?>" rel="home">
                  <?php echo wp_get_attachment_image($logo_id, 'medium'); ?>
                </a>
              </figure>
            <?php else: ?>
              <span class="site-title navbar-item" style="display:block">
                <a href="<?php echo esc_url(home_url('/')); ?>" rel="home"><?php bloginfo('name'); ?></a>
                <?php
                $lemonjelly_description = get_bloginfo('description', 'display');
                if ($lemonjelly_description || is_customize_preview()) : ?>
                  <br /><span class="site-description"><?php echo $lemonjelly_description; ?></span>
                <?php endif; ?>
              </span>
            <?php endif; ?>

            <button class="hamburger" type="button" aria-label="<?php _e('Toggles the website navigation', 'lemonjelly'); ?>" aria-controls="navbar-menu" aria-expanded="false">
              <span class="hamburger-box">
                <span class="hamburger-inner"></span>
              </span>
            </button>
          </div>

          <div id="navbar-menu" class="navbar-menu is-off-canvas">
            <div class="navbar-top">
              <button class="hamburger" type="button" aria-label="<?php _e('Toggles the website navigation', 'lemonjelly'); ?>" aria-controls="navbar-menu" aria-expanded="false">
                <span class="hamburger-label">Menu</span>
                <span class="hamburger-box">
                  <span class="hamburger-inner"></span>
                </span>
              </button>
            </div>


            <div class="navbar-end">
              <?php
              wp_nav_menu(array(
                'theme_location' => 'menu-primary',
                'menu_id'        => 'primary-menu',
                'container'      => false,
              ));
              ?>
            </div>
          </div>
        </div>
      </nav>
    </header>
    <div id="content" class="site-content
">