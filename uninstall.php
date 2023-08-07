<?php

if ( !defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') ) {
    exit();
}

// Delete all portfolio posts and their metadata
$portfolio_posts = get_posts( array( 'post_type' => 'portfolio', 'numberposts' => -1 ) );
foreach ( $portfolio_posts as $portfolio_post ) {
	wp_delete_post( $portfolio_post->ID, true );
}

// Delete all portfolio categories
$portfolio_categories = get_terms( 'portfolio-category', array( 'hide_empty' => false ) );
foreach ( $portfolio_categories as $portfolio_category ) {
	wp_delete_term( $portfolio_category->term_id, 'portfolio-category' );
}
global $wpdb;
$wpdb->query( "DELETE FROM $wpdb->term_taxonomy WHERE taxonomy = 'portfolio-category';" );


// Flush the rewrite rules to remove the custom post type and taxonomy
flush_rewrite_rules();
