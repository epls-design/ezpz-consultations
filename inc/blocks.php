<?php

/**
 * Child Theme Block Registration and Filters
 *
 * @package lemonjelly
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Register ACF Blocks
 */
add_action('acf/init', function () {
  global $lemonjelly_blocks;
  foreach ($lemonjelly_blocks as $slug) {
    $file_path = get_stylesheet_directory(__FILE__) . '/blocks/' . $slug . '/block.json';
    if (file_exists(get_stylesheet_directory(__FILE__) . '/blocks/' . $slug . '/block.json')) {
      register_block_type(get_stylesheet_directory(__FILE__) . '/blocks/' . $slug);
    }
  }
}, 20);

/**
 * Register Child Theme Blocks and remove hero blocks set in the parent theme
 */
add_filter('allowed_block_types_all', function ($allowed_block_types, $editor_context) {

  global $lemonjelly_blocks;
  foreach ($lemonjelly_blocks as $slug) {
    $allowed_block_types[] = 'ezpz/' . $slug;
  }

  $blocks_to_unregister = ['ezpz/hero-post', 'ezpz/hero-page'];
  foreach ($blocks_to_unregister as $block) {
    unset($allowed_block_types[array_search($block, $allowed_block_types)]);
  }

  return $allowed_block_types;
}, 101, 2);



/**
 * Sets up child theme block template
 * @return void
 */
add_action('init', function () {
  $post_type_object = get_post_type_object('page');
  $post_type_object->template = array(
    array('ezpz/lemonjelly-hero', array(
      'lock' => array(
        'move'   => true,
        'remove' => true,
      ),
    )),
    array('ezpz/section', array()),
  );

  $post_type_object = get_post_type_object('post');
  $post_type_object->template = array(
    array('ezpz/lemonjelly-hero', array(
      'lock' => array(
        'move'   => true,
        'remove' => true,
      ),
    )),
    array('ezpz/section', array()),
  );
}, 20);

/**
 * Remove block templates set in the parent theme
 */
add_action('after_setup_theme', function () {
  remove_action('init', 'jellypress_block_templates', 20);
});


/**
 * Filters out put of core/list-item so that if an image is provided it sets the image to the
 * marker-image CSS variable
 * @param string $block_content HTML output of the block
 * @param array $block The block data
 * @return string Updated HTML output
 *
 */
add_filter('render_block_core/list-item', function ($block_content, $block) {
  if (isset($block['attrs']['imageId']) && !empty($block['attrs']['imageId'])) {
    // Initialize the WP_HTML_Tag_Processor with the block content
    $markup = new WP_HTML_Tag_Processor($block_content);

    // Add class and set style attribute
    if ($markup->next_tag()) {
      $image_url = wp_get_attachment_image_url($block['attrs']['imageId'], 'icon');
      $markup->add_class('has-image-marker');
      $markup->set_attribute('style', '--marker-image: url(' . $image_url . ');');
    }

    // Get the updated HTML
    $block_content = $markup->get_updated_html();
  }
  return $block_content;
}, 20, 2);
