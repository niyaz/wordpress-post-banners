<?php
/*
Plugin Name: Wordpress Post Banners
Plugin URI: http://github.com/jystewart/wordpress-post-banners
Description: Making it easier to specify a banner image for a post that isn't part of the post body
Version: 0.1
Author: James Stewart
Author URI: http://jystewart.net/process/
*/

/*  Copyright 2008  James Stewart  (email : james@jystewart.net) */

add_action('admin_menu', 'post_banners_add_custom_box');
add_action('save_post', 'post_banners_process_saved_post');
add_action('admin_menu', 'post_banners_admin_menu');

function post_banners_admin_menu() {
  add_options_page(__('Post Banners', 'post-banners'), __('Post Banners', 'post-banners'), 8, basename(__FILE__), 'post_banners_admin');
}

function post_banners_admin() {
  $options = get_option('post_banners_options');
  if (isset($_POST["post_banners_options_submit"])) {
    $options = array(
      'display_banners_in_feeds' => $_POST['display_banners_in_feeds']
    );
    update_option('post_banners_options', $options);
		$message = __('Options updated.', 'post-banners');
  }
  include dirname(__FILE__) . '/templates/admin.tpl.php';
}

function post_banners_input() {
  include dirname(__FILE__) . '/templates/input_fields.tpl.php';
}

function post_banners_add_custom_box() {
  add_meta_box('post_banners_input', __('Post Banner Image'), 'post_banners_input', 'post', 'normal', 'high');  
}

function post_banners_process_saved_post($post_id) {
  $the_post = wp_is_post_revision($post_id);
  if ($the_post) {
    $post_id = $the_post;
  }

  /* Check whether we have a file uploaded */
  if (isset($_FILES['post_banner_image'])) {
    $key = 'post_banner_image';
    $override = array('test_form' => false);
    $file = wp_handle_upload($_FILES['post_banner_image'], $override);

    if (isset($file['error'])) {
      // handle error
    } else {
      // store the image details in a custom field
      global $wpdb;
      $wpdb->query($wpdb->prepare('REPLACE INTO ' . $wpdb->postmeta . ' (`post_id`, `meta_key`, `meta_value` ) VALUES (%s, %s, %s)', 
        $post_id, 'post_banner_image', $file['url']));
      if (isset($_POST['post_banner_alt'])) {
        $wpdb->query($wpdb->prepare('REPLACE INTO ' . $wpdb->postmeta . ' (`post_id`, `meta_key`, `meta_value` ) VALUES (%s, %s, %s)', 
          $post_id, 'post_banner_alt', htmlspecialchars($_POST['post_banner_alt'])));        
      }
    }
  }
}


?>