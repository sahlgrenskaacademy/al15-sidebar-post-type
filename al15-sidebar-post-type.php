<?php
/**
 * AL 2015 Sidebar Post Type
 *
 * Plugin Name:       AL 2015 Sidebar Post Type
 * Plugin URI:        http://gu.se
 * Description:       Adds the post type Sidebars/Pluggar. Uses custom field "webb". ATT! Require Genesis base theme. Looks better with ACF plugin.
 * Version:           0.2
 * Author:            Pontus Sundén
 * Author URI:        http://gu.se
 * Text Domain:       al15-sidebar
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/// Register post type /////////////////////////////////////////
add_action( 'init', 'psu_al15sidebar_setup_post_type' );
function psu_al15sidebar_setup_post_type() {
	$labels = array(
	  'name' => __( 'Sidebars', 'al15-sidebar' ),
	  'singular_name' => __( 'Sidebar', 'al15-sidebar' ),
	  'add_new' => __( 'New Sidebar', 'al15-sidebar' ),
	  'add_new_item' => __( 'Add New Sidebar', 'al15-sidebar' ),
	  'edit_item' => __( 'Edit Sidebar', 'al15-sidebar' ),
	  'new_item' => __( 'New Sidebar', 'al15-sidebar' ),
	  'view_item' => __( 'View Sidebar', 'al15-sidebar' ),
	  'search_items' => __( 'Search Sidebars', 'al15-sidebar' ),
	  'not_found' =>  __( 'No Sidebars Found', 'al15-sidebar' ),
	  'not_found_in_trash' => __( 'No Sidebars found in Trash', 'al15-sidebar' ),
	);
	$args = array(
	  'labels' => $labels,
	  'has_archive' => false,
	  'public' => true,
	  'hierarchical' => false,
	  'supports' => array(
	    'title',
	    'editor',
//	    'excerpt',
	    'custom-fields',
	    'thumbnail',
	    'page-attributes'
	  ),
	);
	register_post_type( 'al_sidebar_post', $args );
}



/// Add the sidebar area /////////////////////////////////////////
add_action( 'genesis_sidebar', 'psu_al15sidebar_genesis_logic' );
function psu_al15sidebar_genesis_logic() {
	genesis_structural_wrap( 'sidebar' );
	do_action( 'genesis_before_sidebar_widget_area' );
	if (is_active_sidebar( 'al-sidebar' ) ) {
	 	genesis_widget_area( 'al-sidebar' );
	}
	psu_al15sidebar_output();
	do_action( 'genesis_after_sidebar_widget_area' );
	genesis_structural_wrap( 'sidebar', 'close' );
}



/// Output sidebar posts loop /////////////////////////////////////////
// http://code.tutsplus.com/tutorials/use-a-custom-post-type-for-your-sidebar-content--cms-23830
function psu_al15sidebar_output() {

	$args = array(
		'post_type' 			=> 'al_sidebar_post',
		'orderby'					=> 'menu_order',
		'order'						=> 'ASC',
		'posts_per_page' 	=> -1,
	);

	$query = new WP_query ( $args );
	if ( $query->have_posts() ) {

		/* start the loop */
		while ( $query->have_posts() ) : $query->the_post();

			$cf_webb = trim( genesis_get_custom_field('webb')  );
			if ( $cf_webb != '' ) {
				if ( strpos( $cf_webb, 'http' ) === false )
			    $cf_webb = 'http://'.$cf_webb;
			} else {
		  	$cf_webb = false;
		  }

			printf('<aside id="post-%s"', get_the_ID());
				post_class( 'al_sidebar_post' );
			printf('>');

			if ($cf_webb !== false) {
				$link_start 	= sprintf('<a href="%s">', $cf_webb);
				$link_end 		= '</a>';
			} else {
				$link_start 	= '';
				$link_end 		= '';
			}

			if ( has_post_thumbnail() ) {

				echo $link_start;
				the_post_thumbnail( 'al-sidebar', array(
          'class' => 'aligncenter',
          'alt'   => trim(strip_tags( $wp_postmeta->_wp_attachment_image_alt ))
	      ) );
	      echo $link_end;

			} // end thumbnail

			printf('<div class="entry-wrapper"><h2 class="sidebar-title">');
			echo $link_start;
			the_title();
      echo $link_end;

			printf('</h2><section class="sidebar-content">');
			the_content();
			printf('</section></div></aside>');

		endwhile; /* end the loop*/
	  wp_reset_postdata();
	}
}


/// Add the custom field "webb" to post type in admin /////////////////////////////////////////
if( function_exists("register_field_group") ) {
	register_field_group(array (
		'id' => 'psu_al15sidebar_sidebar',
		'title' => __('SidebarX', 'al15-sidebar'),
		'fields' => array (
			array (
				'key' => 'field_55f14b0465ae0',
				'label' => 'Länka till',
				'name' => 'webb',
				'type' => 'text',
				'default_value' => '',
				'placeholder' => 'http://',
				'prepend' => '',
				'append' => '',
				'formatting' => 'html',
				'maxlength' => '',
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'al_sidebar_post',
					'order_no' => 0,
					'group_no' => 0,
				),
			),
		),
		'options' => array (
			'position' => 'normal',
			'layout' => 'default',
			'hide_on_screen' => array (
				0 => 'permalink',
				1 => 'excerpt',
				2 => 'custom_fields',
				3 => 'discussion',
				4 => 'comments',
				5 => 'revisions',
				6 => 'slug',
				7 => 'author',
				8 => 'format',
				9 => 'categories',
				10 => 'tags',
				11 => 'send-trackbacks',
			),
		),
		'menu_order' => 0,
	));
}


/// Activate/Deactivate plugin /////////////////////////////////////////
// https://developer.wordpress.org/plugins/the-basics/activation-deactivation-hooks/

function psu_al15sidebar_install() {
    // trigger our function that registers the custom post type
    psu_al15sidebar_setup_post_type();
    // clear the permalinks after the post type has been registered
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'psu_al15sidebar_install' );

function psu_al15sidebar_deactivation() {
    // unregister the post type, so the rules are no longer in memory
    unregister_post_type( 'al_sidebar_post' );
    // clear the permalinks to remove our post type's rules from the database
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'psu_al15sidebar_deactivation' );
