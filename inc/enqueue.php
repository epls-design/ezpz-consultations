<?php

/**
 * Child Theme Enqueues
 *
 * @package lemonjelly
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Enqueue scripts and styles.
 */
add_action('wp_enqueue_scripts', 'lemonjelly_scripts');
add_action('wp_enqueue_scripts', 'lemonjelly_block_scripts');
add_action('admin_enqueue_scripts', 'lemonjelly_block_scripts');

/**
 * Hooks scripts and styles into the front end only (not the admin)
 *
 * @return void
 */
function lemonjelly_scripts() {

  $style_css_path = get_stylesheet_directory() . '/style.css';
  if (file_exists($style_css_path)) {
    wp_enqueue_style(
      'jellypress-child',
      get_stylesheet_directory_uri() . '/style.css',
      array('jellypress-styles'),
      filemtime($style_css_path)
    );
  }

  $override_css_path = get_stylesheet_directory() . '/lemonjelly.css';
  if (file_exists($override_css_path)) {
    wp_enqueue_style(
      'lemonjelly',
      get_stylesheet_directory_uri() . '/lemonjelly.css',
      array('jellypress-child'),
      filemtime($override_css_path)
    );
  }

  $theme_min_path = get_stylesheet_directory() . '/js/theme.min.js';

  if (file_exists($theme_min_path)) {
    wp_enqueue_script(
      'ezpz-consultations-theme',
      get_stylesheet_directory_uri() . '/js/theme.min.js',
      array('jellypress-scripts'),
      filemtime($theme_min_path)
    );
  }

  // if option ezpz_animation is true, enqueue the animation script
  $enable_animation = get_field('ezpz_animation', 'option');
  if ($enable_animation) {
    $animation_js_path = get_stylesheet_directory() . '/js/animate-scroll.js';
    if (file_exists($animation_js_path)) {
      wp_enqueue_script(
        'ezpz-animate-scroll',
        get_stylesheet_directory_uri() . '/js/animate-scroll.js',
        array('jellypress-scripts'),
        filemtime($animation_js_path),
        true
      );
    }
  }

  // Enqueue lemonjelly.js
  $custom_js_path = get_stylesheet_directory() . '/lemonjelly.js';
  if (file_exists($custom_js_path)) {
    wp_enqueue_script(
      'lemonjelly-custom-js',
      get_stylesheet_directory_uri() . '/lemonjelly.js',
      array(),
      filemtime($custom_js_path),
      true
    );
  }
}

/**
 * Hooks assets that can run on the front end or the block editor
 */
function lemonjelly_block_scripts() {
}


add_action('admin_init', 'lemonjelly_editor_styles', 500);
function lemonjelly_editor_styles() {
  add_editor_style('style.css');
  add_editor_style('lemonjelly.css');
}


add_action('wp_head', function () {
  $acf_opts = get_fields('options');
  if (isset($acf_opts['unfiltered_html'])) {
    echo $acf_opts['unfiltered_html'];
  }
});

add_action('wp_head', function () {
  $bugherd = get_field('bugherd', 'option');
  $bugherd_script = get_field('bugherd_script', 'option');
  if ($bugherd && $bugherd_script) {
    echo $bugherd_script;
  }
});


add_action('enqueue_block_editor_assets', 'lemonjelly_block_filters');
function lemonjelly_block_filters() {
  wp_enqueue_script(
    'lemonjelly-block-filters',
    get_stylesheet_directory_uri() . '/js/editor-filters.js',
    array('react', 'react-dom', 'wp-data', 'wp-blocks', 'wp-dom-ready', 'wp-edit-post', 'wp-hooks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'lodash'),
    filemtime(get_template_directory() . '/js/editor-filters.js'),
  );
}


add_action('wp_head', 'lemonjelly_frontend_favicon');
function lemonjelly_frontend_favicon() {
  $favicon_url = get_field('favicon', 'option');
  $favicon_url  = wp_get_attachment_image_url($favicon_url, 'icon');
  // Check if favicon URL exists
  if ($favicon_url) {
    // Update favicon links with the ACF URL
    echo '<link rel="shortcut icon" type="image/x-icon" href="' . esc_url($favicon_url) . '">';
    echo '<link rel="icon" type="image/png" href="' . esc_url($favicon_url) . '" sizes="194x194">';
    echo '<link rel="icon" type="image/png" href="' . esc_url($favicon_url) . '" sizes="96x96">';
    echo '<link rel="icon" type="image/png" href="' . esc_url($favicon_url) . '" sizes="32x32">';
    echo '<link rel="icon" type="image/png" href="' . esc_url($favicon_url) . '" sizes="16x16">';
    echo '<link rel="apple-touch-icon" href="' . esc_url($favicon_url) . '">';
    echo '<link rel="mask-icon" href="' . esc_url($favicon_url) . '" color="#5bbad5">';
  }
}

// Add favicon to admin areas
add_action('login_head', 'lemonjelly_backend_favicon');
add_action('admin_head', 'lemonjelly_backend_favicon');
function lemonjelly_backend_favicon() {
  if (function_exists('get_field')) {

    // Get the URL of the uploaded favicon image from ACF
    $favicon_url = get_field('favicon', 'option');
    $favicon_url  = wp_get_attachment_image_url($favicon_url, 'icon');
    // Check if favicon URL exists
    if ($favicon_url) {
      // Update favicon link in admin areas with the ACF URL
      echo '<link rel="shortcut icon" type="image/x-icon" href="' . esc_url($favicon_url) . '">';
    }
  }
}

add_action('wp_footer', 'lemonjelly_output_custom_js', 1000);

function lemonjelly_output_custom_js() {
  $acf_opts = get_fields('options');

  if (!empty($acf_opts['js'])) {
    echo '<script id="custom-acf-js">';
    echo $acf_opts['js'];
    echo '</script>';
  }
}
