<?php

/**
 * Theme filters.
 */

namespace App;

/**
 * Add "… Continued" to the excerpt.
 *
 * @return string
 */
add_filter('excerpt_more', function () {
    return ' &hellip;';
});


/**
  * Change exceprt length
**/
$length = function() {
  return 18;
};
add_filter('excerpt_length', $length);


/**
 * Remove category name
 */
add_filter( 'get_the_archive_title', function ( $title ) {
  if( is_category() ) {
      $title = single_cat_title( '', false );
  } elseif ( is_tag() ) {
      $title = single_tag_title( '', false );
  }
  return $title;
});


/**
 * Remove 'category' base from category archives only
 * (does not affect post permalinks with /%category%/%postname%/)
 */
add_action('init', function () {
    global $wp_rewrite;
    $wp_rewrite->category_base = '';
});

add_filter('category_link', function ($link) {
    // Remove /category/ from category archive links
    return str_replace('/category/', '/', $link);
}, 10, 1);

/**
 * Formulieren
 * "" verwijderen
 */
add_filter('gform_required_legend', function ($legend, $form) {
    return '<p class="gform_required_legend text-xs"><span class="gfield_required gfield_required_asterisk">*</span> Verplichte velden.</p>';
}, 10, 2);