<?php

/**
 * Pattern template for new post creation.
 * Must return an array with 'pattern' key.
 * 'title' is optional, will default to file name if not provided.
 * 'post_types' is optional, defaults to 'page' if not provided.
 * Description and Image are optional.
 *
 * @package jellypress
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;

return [
  'title' => __('Default', 'jellypress'),
  'description' => __('A default layout for posts with a hero section and content area.', 'jellypress'),
  'post_types' => array('post'),
  // 'image' => get_template_directory_uri() . '/screenshot.png',
  'pattern' => array(
    array('ezpz/lemonjelly-hero', array(
      'lock' => array(
        'move'   => true,
        'remove' => true,
      ),
    )),
    array('ezpz/section', array()),
  )
];
