<?php

/**
 * Child Theme ACF Hooks and Filters
 *
 * @package lemonjelly
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Adds ACF options page
 */
if (function_exists('acf_add_options_page')) {
  acf_add_options_page(
    array(
      'page_title'   => __('Theme Designer', 'lemonjelly'),
      'menu_title'  => __('Theme Designer', 'lemonjelly'),
      'menu_slug'   => 'theme-designer',
      'capability'  => 'edit_posts',
      'icon_url' => 'dashicons-art',
      'position' => 2,
      'autoload' => true, // Speeds up load times
      'updated_message' => __("Successfully updated theme design", 'lemonjelly'),
    )
  );
}

/**
 * Save and load ACF local JSON
 */
add_action('acf/update_field_group', 'lemonjelly_save_acf_local_json', 1, 1);
function lemonjelly_save_acf_local_json($group) {
  $groups = array(
    'group_65d61eb9da7c1', // THEME DESIGNER
    'group_65e9b645cb80d', // BLOCK: HERO
    'group_64c2957a5ef4e', // BLOCK: TIMELINE
    'group_65e6eb7aed060', // BLOCK: COMPARE
    'group_6650768a4a243', // BLOCK: MAP
  );

  if (in_array($group['key'], $groups)) {
    add_filter('acf/settings/save_json', function () {
      return get_stylesheet_directory() . '/acf-json';
    });
  }
}
add_action('acf/settings/load_json', 'lemonjelly_load_acf_local_json', 1, 1);

function lemonjelly_load_acf_local_json($paths) {
  $paths[] = get_stylesheet_directory() . '/acf-json';


  // Check if there are any in the /blocks directory
  $blocks = get_stylesheet_directory() . '/blocks';
  if (is_dir($blocks)) {
    $block_folders = array_diff(scandir($blocks), array('..', '.'));
    foreach ($block_folders as $block_folder) {
      $block_folder_path = $blocks . '/' . $block_folder;
      if (is_dir($block_folder_path)) {
        $block_json = $block_folder_path . '/block.json';
        if (file_exists($block_json)) {
          $paths[] = $block_folder_path;
        }
      }
    }
  }
  return $paths;
}

add_filter('acf/update_value', 'lemonjelly_kses_acf', 1, 3);
function lemonjelly_kses_acf($data, $post_id, $field) {
  if (!is_array($data)) {
    // If it's not an array, sanitize
    if ($field['_name'] != 'unfiltered_html' || $field['_name'] != 'bugherd_script') {
      // return wp_kses_post($data);
      return $data;
    } else {
      // if fieldName = 'unfiltered_html' don't sanitize
      return $data;
    }
  }
  $return = array();
  if (count($data)) {
    // If it's an array (eg. repeater, group, etc) repeat this function on each value
    foreach ($data as $index => $value) {
      $return[$index] = lemonjelly_kses_acf($value, $post_id, $field);
    }
  }
  return $return;
}
