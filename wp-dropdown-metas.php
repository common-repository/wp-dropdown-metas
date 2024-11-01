<?php
/*
Plugin Name: WP Dropdown Metas
Plugin URI : http://www.malaiac.com/wp-dropdown-metas
Description: Allow a dropdown of a selected custom key.<br />Basic usage : wp_dropddown_metas('meta_key=my_key'); 
Author: Fluenx
Version: 1.2
Author URI: https://www.fluenx.com/
*/

function wp_dropdown_metas($args = '') {
	global $wpdb;
	$defaults = array(
		'show_option_all' => '', 'show_option_none' => '',
		'orderby' => 'meta_value', 'order' => 'ASC',
		'hide_empty' => 1, 'exclude' => '', 
		'echo' => 1, 'selected' => 0, 
		'meta_key' => false, // required
		'name' => 'meta_key', 'class' => 'meta_key',
		'tab_index' => 0, 'show_count' => 1,
	);
	
	$r = wp_parse_args( $args, $defaults );
	extract( $r );
	
	$get_posts_count = ($orderby == 'count' || $show_count) ? true : false;
	
	if(!$meta_key) return;
	
	$tab_index_attribute = '';
	if ( (int) $tab_index > 0 )
	$tab_index_attribute = " tabindex=\"$tab_index\"";
	
	$query = "SELECT DISTINCT(m.meta_value) ";
	if($get_posts_count) 
	 $query .= ", COUNT(p.ID) as count ";
	$query .= "FROM $wpdb->postmeta m ";
	if($hide_empty) 
	 $query .= "JOIN $wpdb->posts p ON p.ID = m.post_id ";
	$query .= "WHERE m.meta_key = %s ";
	if($hide_empty) 
	 $query .= "AND p.post_status = 'publish' ";
	if($get_posts_count) 
	 $query .= "GROUP BY meta_value ";
	$query = $wpdb->prepare($query , $meta_key);
	
	$meta_values = $wpdb->get_results($query);
	
	$output = '';
	if ( ! empty( $meta_values ) ) {
		$output = "<select name='$name' id='$name' class='$class' $tab_index_attribute>\n";
		
		if ( $show_option_none ) {
		 $output .= "\t<option value='-1'>$show_option_none</option>\n";
		 }
		
		foreach($meta_values as $meta_value) {
			$output .= "\t<option value='$meta_value->meta_value'>$meta_value->meta_value";
			if($show_count) $output .= " ($meta_value->count)";
			$output .= "</option>\n";
			}
		$output .= "</select>\n";
	}
	
	$output = apply_filters( 'wp_dropdown_metas', $output );
	if ( $echo )
	 echo $output;
	return $output;
}		
