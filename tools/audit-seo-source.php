<?php
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) exit;
global $wpdb;
foreach ( array( 'posts' => array( 'post_content', 'post_excerpt' ), 'postmeta' => array( 'meta_value' ), 'options' => array( 'option_value' ) ) as $table => $columns ) {
  foreach ( $columns as $column ) {
    $name = $wpdb->prefix . $table;
    $count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `$name` WHERE `$column` LIKE %s", '%globalexposomesummit%' ) );
    echo "$table.$column: $count\n";
  }
}
