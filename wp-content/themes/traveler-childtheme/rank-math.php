<?php
/**
 * Filter to enable Rank Math SEO on Elementor templates
 */
add_filter( 'rank_math/excluded_post_types',function( $post_types) {
      $post_types['elementor_library'] = 'elementor_library';
     return $post_types;
}, 11 );
