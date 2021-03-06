<?php
/**
 * Plugin Name: Breadcumbs Plus
 * Plugin URI: http://snippets-tricks.org/proyectos/tilt-plugin/
 * Description: Breadcrumbs Plus provide links back to each previous page the user navigated through to get to the current page or-in hierarchical site structures-the parent pages of the current one.
 * Version: 0.4
 * Author: Luis Alberto Ochoa Esparza
 * Author URI: http://luisalberto.org
 * License: GPL2
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation.  You may NOT assume
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * You should have received a copy of the GNU General Public License along with this program; if not, write
 * to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 * @package Breadcrumbs Plus
 * @version 0.4
 * @author Luis Alberto Ochoa Esparza <soy@luisalberto.org>
 * @copyright Copyright (c) 2010-2011, Luis Alberto Ochoa
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

function wtbx_bc_plus( $args = '' ) {

	/* Set up the default arguments for the breadcrumb. */
	$defaults = array(
		'prefix' => '<div>',
		'suffix' => '</div>',
		'title' => '',
		'home' => esc_html__( 'Home', 'scape' ),
		'separator' => '/',
		'front_page' => true,
		'show_blog' => true,
		'singular_post_taxonomy' => 'category',
		'echo' => true
	);

	$args = apply_filters( 'wtbx_bc_plus_args', $args );

	$args = wp_parse_args( $args, $defaults );

	if ( is_front_page() && !$args['front_page'] )
		return apply_filters( 'wtbx_bc_plus', false );

	/* Format the title. */
	$title = ( !empty( $args['title'] ) ? '<span class="breadcrumbs-title">' . esc_html($args['title']) . '</span>': '' );

	$separator = ( !empty( $args['separator'] ) ) ? "<span class='separator'>{$args['separator']}</span>" : "<span class='separator'>/</span>";

	/* Get the items. */
	$items = wtbx_bc_plus_get_items( $args );

	$breadcrumbs  = '<div class="breadcrumbs-path">';
	$breadcrumbs .= $args['prefix'];
	$breadcrumbs .= $title;
	$breadcrumbs .= join( " {$separator} ", $items );
	$breadcrumbs .= $args['suffix'];
	$breadcrumbs .= '</div>';

	$breadcrumbs = apply_filters( 'wtbx_bc_plus', $breadcrumbs );

	$breadcrumbs_escaped = $breadcrumbs;

	if ( !$args['echo'] )
		return $breadcrumbs_escaped;
	else
		// This variable contains dynamic values that have been safely escaped above
		echo $breadcrumbs_escaped;
}

/**
 * Gets the items for the breadcrumbs plus.
 *
 * @since 0.4
 */
function wtbx_bc_plus_get_items( $args ) {
	global $wp_query;

	$item = array();

	$show_on_front = get_option( 'show_on_front' );

	/* Front page. */
	if ( is_front_page() ) {
		$item['last'] = $args['home'];
	}

	/* Link to front page. */
	if ( !is_front_page() )
		$item[] = '<a href="'. esc_url( home_url( '/' ) ) .'" class="home">' . $args['home'] . '</a>';

	/* If bbPress is installed and we're on a bbPress page. */
	if ( function_exists( 'is_bbpress' ) && is_bbpress() )
		$item = array_merge( $item, wtbx_bc_plus_get_bbpress_items() );

	/* If viewing a home/post page. */
	elseif ( is_home() ) {
		$home_page = get_post( $wp_query->get_queried_object_id() );
		$item = array_merge( $item, wtbx_bc_plus_get_parents( $home_page->post_parent ) );
		if ( !is_front_page() ) {
			$item['last'] = get_the_title( $home_page->ID );
		}
	}

	/* If viewing a singular post. */
	elseif ( is_singular() ) {

		$post = $wp_query->get_queried_object();
		$post_id = (int) $wp_query->get_queried_object_id();
		$post_type = $post->post_type;

		$post_type_object = get_post_type_object( $post_type );

		if ( 'post' === $wp_query->post->post_type && $args['show_blog'] && get_option( 'page_for_posts' ) ) {
			if ( !empty(get_post_meta(get_the_id(), 'navigation-parent', true))) {
				$nav_index = get_post_meta(get_the_id(), 'navigation-parent', true);
				$item[] = '<a href="' . esc_url( get_permalink($nav_index) ) . '">' . get_the_title($nav_index) . '</a>';
			} else {
				$item[] = '<a href="' . esc_url( get_permalink( get_option( 'page_for_posts' ) ) ) . '">' . get_the_title( get_option( 'page_for_posts' ) ) . '</a>';
			}
		}

		if ( 'page' !== $wp_query->post->post_type ) {

			/* If there's an archive page, add it. */
			if ( function_exists( 'get_post_type_archive_link' ) && !empty( $post_type_object->has_archive ) ) {
				$archive_label = $post_type_object->labels->name;
				if ( 'portfolio' === $wp_query->post->post_type && !empty(get_post_meta(get_the_id(), 'navigation-parent', true))) {
					$nav_index = get_post_meta(get_the_id(), 'navigation-parent', true);
					$item[] = '<a href="' . esc_url( get_permalink($nav_index) ) . '">' . get_the_title($nav_index) . '</a>';
				} else {
					if ( class_exists('Woocommerce') && is_woocommerce() ) {
						$archive_label = get_the_title(wc_get_page_id('shop'));
					}
					$item[] = '<a href="' . esc_url( get_post_type_archive_link( $post_type ) ) . '" title="' . esc_attr( $post_type_object->labels->name ) . '">' . $archive_label . '</a>';
				}
			}

			if ( isset( $args["singular_{$wp_query->post->post_type}_taxonomy"] ) && is_taxonomy_hierarchical( $args["singular_{$wp_query->post->post_type}_taxonomy"] ) ) {
				$terms = wp_get_object_terms( $post_id, $args["singular_{$wp_query->post->post_type}_taxonomy"] );
				$item = array_merge( $item, wtbx_bc_plus_get_term_parents( $terms[0], $args["singular_{$wp_query->post->post_type}_taxonomy"] ) );
			}

			elseif ( isset( $args["singular_{$wp_query->post->post_type}_taxonomy"] ) )
				$item[] = get_the_term_list( $post_id, $args["singular_{$wp_query->post->post_type}_taxonomy"], '', ', ', '' );
		}

		if ( ( is_post_type_hierarchical( $wp_query->post->post_type ) || 'attachment' === $wp_query->post->post_type ) && $parents = wtbx_bc_plus_get_parents( $wp_query->post->post_parent ) ) {
			$item = array_merge( $item, $parents );
		}

		$item['last'] = get_the_title();
	}

	/* If viewing any type of archive. */
	else if ( is_archive() ) {

		if ( is_category() || is_tag() || is_tax() ) {

			$term = $wp_query->get_queried_object();
			$taxonomy = get_taxonomy( $term->taxonomy );

			$post_type = get_post_type();

			if ( class_exists('Woocommerce') && is_woocommerce() ) {
				$parent_label = get_the_title( wc_get_page_id('shop') );
			} elseif ( 'post' === $post_type ) {
				if ( !empty(get_option('page_for_posts')) ) {
					$parent_label = get_the_title( get_option('page_for_posts') );
				}
			} else {
				$parent_label = get_post_type_object( $post_type )->labels->name;
			}


			if ( ( 'post' === $wp_query->post->post_type || 'portfolio' === $wp_query->post->post_type ) && !empty(get_post_meta(get_the_id(), 'navigation-parent', true))) {
				$nav_index = get_post_meta($wp_query->post->ID, 'navigation-parent', true);
				$item[] = '<a href="' . get_permalink($nav_index) . '">' . get_the_title($nav_index) . '</a>';
			} else {
				if ( get_option('page_for_posts') || get_option( 'page_on_front' ) ) {
					if ( class_exists('Woocommerce') && is_woocommerce() ) {
						$parent_url = get_the_permalink( wc_get_page_id('shop') );
					} else {
						$parent_url = get_post_type_archive_link( get_post_type(wtbx_get_the_id()) );
					}

					if ( !empty($parent_label) ) {
						$item[] = '<a href="' . esc_url( $parent_url ) . '" title="' . esc_attr( $parent_label ) . '">' . esc_html( $parent_label ) . '</a>';
					}

				}
			}

			if ( ( is_taxonomy_hierarchical( $term->taxonomy ) && $term->parent ) && $parents = wtbx_bc_plus_get_term_parents( $term->parent, $term->taxonomy ) )
				$item = array_merge( $item, $parents );

			$item['last'] = $term->name;
		}

		else if ( function_exists( 'is_post_type_archive' ) && is_post_type_archive() ) {
			$post_type_object = get_post_type_object( get_query_var( 'post_type' ) );

			$post_type = get_post_type();

			if ( class_exists('Woocommerce') && is_woocommerce() ) {
				$archive_label = get_the_title( wc_get_page_id('shop') );
			} elseif ( 'post' === $post_type ) {
				$archive_label = get_the_title( get_option('page_for_posts') );
			} else {
				$archive_label = get_post_type_object( $post_type )->labels->name;
			}

			$item['last'] = $archive_label;
		}

		else if ( is_date() ) {

			if ( is_day() )
				$item['last'] = esc_html__( 'Archives for ', 'scape' ) . get_the_time( 'F j, Y' );

			elseif ( is_month() )
				$item['last'] = esc_html__( 'Archives for ', 'scape' ) . single_month_title( ' ', false );

			elseif ( is_year() )
				$item['last'] = esc_html__( 'Archives for ', 'scape' ) . get_the_time( 'Y' );
		}

		else if ( is_author() )
			$item['last'] = esc_html__( 'Archives by: ', 'scape' ) . get_the_author_meta( 'display_name', $wp_query->post->post_author );
	}

	/* If viewing search results. */
	else if ( is_search() )
		$item['last'] = esc_html__( 'Search results for "', 'scape' ) . stripslashes( strip_tags( get_search_query() ) ) . '"';

	/* If viewing a 404 error page. */
	else if ( is_404() )
		$item['last'] = esc_html__( 'Page Not Found', 'scape' );

	return apply_filters( 'wtbx_bc_plus_items', $item );
}

/**
 * Gets the items for the breadcrumb item if bbPress is installed.
 *
 * @since 0.4
 *
 * @param array $args Mixed arguments for the menu.
 * @return array List of items to be shown in the item.
 */
function wtbx_bc_plus_get_bbpress_items( $args = array() ) {

	$item = array();

	$post_type_object = get_post_type_object( bbp_get_forum_post_type() );

	if ( !empty( $post_type_object->has_archive ) && !bbp_is_forum_archive() )
		$item[] = '<a href="' . esc_url( get_post_type_archive_link( bbp_get_forum_post_type() ) ) . '">' . bbp_get_forum_archive_title() . '</a>';

	if ( bbp_is_forum_archive() )
		$item[] = bbp_get_forum_archive_title();

	elseif ( bbp_is_topic_archive() )
		$item[] = bbp_get_topic_archive_title();

	elseif ( bbp_is_single_view() )
		$item[] = bbp_get_view_title();

	elseif ( bbp_is_single_topic() ) {

		$topic_id = get_queried_object_id();

		$item = array_merge( $item, wtbx_bc_plus_get_parents( bbp_get_topic_forum_id( $topic_id ) ) );

		if ( bbp_is_topic_split() || bbp_is_topic_merge() || bbp_is_topic_edit() )
			$item[] = '<a href="' . esc_url( bbp_get_topic_permalink( $topic_id ) ) . '">' . bbp_get_topic_title( $topic_id ) . '</a>';
		else
			$item[] = bbp_get_topic_title( $topic_id );

		if ( bbp_is_topic_split() )
			$item[] = esc_html__( 'Split', 'scape' );

		elseif ( bbp_is_topic_merge() )
			$item[] = esc_html__( 'Merge', 'scape' );

		elseif ( bbp_is_topic_edit() )
			$item[] = esc_html__( 'Edit', 'scape' );
	}

	elseif ( bbp_is_single_reply() ) {

		$reply_id = get_queried_object_id();

		$item = array_merge( $item, wtbx_bc_plus_get_parents( bbp_get_reply_topic_id( $reply_id ) ) );

		if ( !bbp_is_reply_edit() ) {
			$item[] = bbp_get_reply_title( $reply_id );

		} else {
			$item[] = '<a href="' . esc_url( bbp_get_reply_url( $reply_id ) ) . '">' . bbp_get_reply_title( $reply_id ) . '</a>';
			$item[] = esc_html__( 'Edit', 'scape' );
		}

	}

	elseif ( bbp_is_single_forum() ) {

		$forum_id = get_queried_object_id();
		$forum_parent_id = bbp_get_forum_parent( $forum_id );

		if ( 0 !== $forum_parent_id)
			$item = array_merge( $item, wtbx_bc_plus_get_parents( $forum_parent_id ) );

		$item[] = bbp_get_forum_title( $forum_id );
	}

	elseif ( bbp_is_single_user() || bbp_is_single_user_edit() ) {

		if ( bbp_is_single_user_edit() ) {
			$item[] = '<a href="' . esc_url( bbp_get_user_profile_url() ) . '">' . bbp_get_displayed_user_field( 'display_name' ) . '</a>';
			$item[] = esc_html__( 'Edit', 'scape' );
		} else {
			$item[] = bbp_get_displayed_user_field( 'display_name' );
		}
	}

	return apply_filters( 'wtbx_bc_plus_get_bbpress_items', $item, $args );
}

/**
 * Gets parent pages of any post type.
 *
 * @since 0.1
 * @param int $post_id ID of the post whose parents we want.
 * @param string $separator.
 * @return string $html String of parent page links.
 */
function wtbx_bc_plus_get_parents( $post_id = '', $separator = '/' ) {

	$parents = array();

	if ( $post_id == 0 )
		return $parents;

	while ( $post_id ) {
		$page = get_page( $post_id );
		$parents[]  = '<a href="' . esc_url( get_permalink( $post_id ) ) . '" title="' . esc_attr( get_the_title( $post_id ) ) . '">' . get_the_title( $post_id ) . '</a>';
		$post_id = $page->post_parent;
	}

	if ( $parents )
		$parents = array_reverse( $parents );

	return $parents;
}

/**
 * Searches for term parents of hierarchical taxonomies.
 *
 * @since 0.1
 * @param int $parent_id The ID of the first parent.
 * @param object|string $taxonomy The taxonomy of the term whose parents we want.
 * @return string $html String of links to parent terms.
 */
function wtbx_bc_plus_get_term_parents( $parent_id = '', $taxonomy = '', $separator = '/' ) {

	$html = array();
	$parents = array();

	if ( empty( $parent_id ) || empty( $taxonomy ) )
		return $parents;

	while ( $parent_id ) {
		$parent = get_term( $parent_id, $taxonomy );
		$parents[] = '<a href="' . esc_url( get_term_link( $parent, $taxonomy ) ) . '" title="' . esc_attr( $parent->name ) . '">' . $parent->name . '</a>';
		$parent_id = $parent->parent;
	}

	if ( $parents )
		$parents = array_reverse( $parents );

	return $parents;
}

/**
 * Try to add automatically to Hybrid, Thematic, Thesis and Genesis
 *
 * @since 0.1
 * @return string
 */
function setup_wtbx_bc_plus() {

	/* Hybrid */
	remove_action( 'hybrid_before_content', 'hybrid_breadcrumb' );
	add_action( 'hybrid_before_content', 'wtbx_bc_plus' );

	/* Thematic */
	add_action( 'thematic_belowheader','wtbx_bc_plus' );

	/* Thesis */
	add_action( 'thesis_hook_before_content','wtbx_bc_plus' );

	/* Genesis */
	remove_action( 'genesis_before_loop', 'genesis_do_breadcrumbs' );
	add_action( 'genesis_before_loop', 'wtbx_bc_plus' );
}

add_action( 'init', 'setup_wtbx_bc_plus' );
