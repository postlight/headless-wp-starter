<?php

/*
*  acf_get_taxonomies
*
*  Returns an array of taxonomy names.
*
*  @date	7/10/13
*  @since	5.0.0
*
*  @param	array $args An array of args used in the get_taxonomies() function.
*  @return	array An array of taxonomy names.
*/

function acf_get_taxonomies( $args = array() ) {

	// vars
	$taxonomies = array();
	
	// get taxonomy objects
	$objects = get_taxonomies( $args, 'objects' );
	
	// loop
	foreach( $objects as $i => $object ) {
		
		// bail early if is builtin (WP) private post type
		// - nav_menu_item, revision, customize_changeset, etc
		if( $object->_builtin && !$object->public ) continue;
		
		// append
		$taxonomies[] = $i;
	}
	
	// custom post_type arg which does not yet exist in core
	if( isset($args['post_type']) ) {
		$taxonomies = acf_get_taxonomies_for_post_type($args['post_type']);
	}
	
	// filter
	$taxonomies = apply_filters('acf/get_taxonomies', $taxonomies, $args);
	
	// return
	return $taxonomies;
}

/**
*  acf_get_taxonomies_for_post_type
*
*  Returns an array of taxonomies for a given post type(s)
*
*  @date	7/9/18
*  @since	5.7.5
*
*  @param	string|array $post_types The post types to compare against.
*  @return	array
*/
function acf_get_taxonomies_for_post_type( $post_types = 'post' ) {
	
	// vars
	$taxonomies = array();
	
	// loop
	foreach( (array) $post_types as $post_type ) {
		$object_taxonomies = get_object_taxonomies( $post_type );
		foreach( (array) $object_taxonomies as $taxonomy ) {
			$taxonomies[] = $taxonomy;						
		}
	}
	
	// remove duplicates
	$taxonomies = array_unique($taxonomies);
	
	// return
	return $taxonomies;
}

/*
*  acf_get_taxonomy_labels
*
*  Returns an array of taxonomies in the format "name => label" for use in a select field.
*
*  @date	3/8/18
*  @since	5.7.2
*
*  @param	array $taxonomies Optional. An array of specific taxonomies to return.
*  @return	array
*/

function acf_get_taxonomy_labels( $taxonomies = array() ) {
	
	// default
	if( empty($taxonomies) ) {
		$taxonomies = acf_get_taxonomies();
	}
	
	// vars
	$ref = array();
	$data = array();
	
	// loop
	foreach( $taxonomies as $taxonomy ) {
		
		// vars
		$object = get_taxonomy( $taxonomy );
		$label = $object->labels->singular_name;
		
		// append
		$data[ $taxonomy ] = $label;
		
		// increase counter
		if( !isset($ref[ $label ]) ) {
			$ref[ $label ] = 0;
		}
		$ref[ $label ]++;
	}
	
	// show taxonomy name next to label for shared labels
	foreach( $data as $taxonomy => $label ) {
		if( $ref[$label] > 1 ) {
			$data[ $taxonomy ] .= ' (' . $taxonomy . ')';
		}
	}
	
	// return
	return $data;
}

/**
*  acf_get_term_title
*
*  Returns the title for this term object.
*
*  @date	10/9/18
*  @since	5.0.0
*
*  @param	object $term The WP_Term object.
*  @return	string
*/

function acf_get_term_title( $term ) {
	
	// set to term name
	$title = $term->name;
	
	// allow for empty name
	if( $title === '' ) {
		$title = __('(no title)', 'acf');
	}
	
	// prepent ancestors indentation
	if( is_taxonomy_hierarchical($term->taxonomy) ) {
		$ancestors = get_ancestors( $term->term_id, $term->taxonomy );
		$title = str_repeat('- ', count($ancestors)) . $title;
	}
	
	// return
	return $title;
}

/**
*  acf_get_grouped_terms
*
*  Returns an array of terms for the given query $args and groups by taxonomy name.
*
*  @date	2/8/18
*  @since	5.7.2
*
*  @param	array $args An array of args used in the get_terms() function.
*  @return	array
*/

function acf_get_grouped_terms( $args ) {
	
	// vars
	$data = array();
	
	// defaults
	$args = wp_parse_args($args, array(
		'taxonomy'					=> null,
		'hide_empty'				=> false,
		'update_term_meta_cache'	=> false,
	));
	
	// vars
	$taxonomies = acf_get_taxonomy_labels( acf_get_array($args['taxonomy']) );
	$is_single = (count($taxonomies) == 1);
	
	// specify exact taxonomies required for _acf_terms_clauses() to work.
	$args['taxonomy'] = array_keys($taxonomies);
	
	// add filter to group results by taxonomy
	if( !$is_single ) {
		add_filter('terms_clauses', '_acf_terms_clauses', 10, 3);
	}
	
	// get terms
	$terms = get_terms( $args );
	
	// remove this filter (only once)
	if( !$is_single ) {
		remove_filter('terms_clauses', '_acf_terms_clauses', 10, 3);
	}
	
	// loop
	foreach( $taxonomies as $taxonomy => $label ) {
		
		// vars
		$this_terms = array();
		
		// populate $this_terms
		foreach( $terms as $term ) {
			if( $term->taxonomy == $taxonomy ) {
				$this_terms[] = $term;
			}
		}
		
		// bail early if no $items
		if( empty($this_terms) ) continue;
		
		// sort into hierachial order
		// this will fail if a search has taken place because parents wont exist
		if( is_taxonomy_hierarchical($taxonomy) && empty($args['s'])) {
			
			// get all terms from this taxonomy
			$all_terms = get_terms(array_merge($args, array(
				'number'		=> 0,
				'offset'		=> 0,
				'taxonomy'		=> $taxonomy
			)));
			
			// vars
			$length = count($this_terms);
			$offset = 0;
			
			// find starting point (offset)
			foreach( $all_terms as $i => $term ) {
				if( $term->term_id == $this_terms[0]->term_id ) {
					$offset = $i;
					break;
				}
			}
			
			// order terms
			$parent = acf_maybe_get( $args, 'parent', 0 );
			$parent = acf_maybe_get( $args, 'child_of', $parent );
			$ordered_terms = _get_term_children( $parent, $all_terms, $taxonomy );
			
			// compare aray lengths
			// if $ordered_posts is smaller than $all_posts, WP has lost posts during the get_page_children() function
			// this is possible when get_post( $args ) filter out parents (via taxonomy, meta and other search parameters)
			if( count($ordered_terms) == count($all_terms) ) {
				$this_terms = array_slice($ordered_terms, $offset, $length);
			}
		}
		
		// populate group
		$data[ $label ] = array();
		foreach( $this_terms as $term ) {
			$data[ $label ][ $term->term_id ] = $term;
		}	
	}
	
	// return
	return $data;
}

/**
*  _acf_terms_clauses
*
*  Used in the 'terms_clauses' filter to order terms by taxonomy name.
*
*  @date	2/8/18
*  @since	5.7.2
*
*  @param	array $pieces     Terms query SQL clauses.
*  @param	array $taxonomies An array of taxonomies.
*  @param	array $args       An array of terms query arguments.
*  @return	array $pieces
*/

function _acf_terms_clauses( $pieces, $taxonomies, $args ) {
	
	// prepend taxonomy to 'orderby' SQL
	if( is_array($taxonomies) ) {
		$sql = "FIELD(tt.taxonomy,'" . implode("', '", array_map('esc_sql', $taxonomies)) . "')";
		$pieces['orderby'] = str_replace("ORDER BY", "ORDER BY $sql,", $pieces['orderby']);
	}
	
	// return	
	return $pieces;
}

/**
*  acf_get_pretty_taxonomies
*
*  Deprecated in favor of acf_get_taxonomy_labels() function.
*
*  @date		7/10/13
*  @since		5.0.0
*  @deprecated	5.7.2
*/

function acf_get_pretty_taxonomies( $taxonomies = array() ) {
	return acf_get_taxonomy_labels( $taxonomies );
}

/**
*  acf_get_term
*
*  Similar to get_term() but with some extra functionality.
*
*  @date	19/8/18
*  @since	5.7.3
*
*  @param	mixed $term_id The term ID or a string of "taxonomy:slug".
*  @param	string $taxonomy The taxonomyname.
*  @return	WP_Term
*/

function acf_get_term( $term_id, $taxonomy = '' ) {
	
	// allow $term_id parameter to be a string of "taxonomy:slug" or "taxonomy:id"
	if( is_string($term_id) && strpos($term_id, ':') ) {
		list( $taxonomy, $term_id ) = explode(':', $term_id);
		$term = get_term_by( 'slug', $term_id, $taxonomy );
		if( $term ) return $term;
	}
	
	// return
	return get_term( $term_id, $taxonomy );
}

/**
*  acf_encode_term
*
*  Returns a "taxonomy:slug" string for a given WP_Term.
*
*  @date	27/8/18
*  @since	5.7.4
*
*  @param	WP_Term $term The term object.
*  @return	string
*/
function acf_encode_term( $term ) {
	return "{$term->taxonomy}:{$term->slug}";
}

/**
*  acf_decode_term
*
*  Decodes a "taxonomy:slug" string into an array of taxonomy and slug.
*
*  @date	27/8/18
*  @since	5.7.4
*
*  @param	WP_Term $term The term object.
*  @return	string
*/
function acf_decode_term( $string ) {
	if( is_string($string) && strpos($string, ':') ) {
		list( $taxonomy, $slug ) = explode(':', $string);
		return array(
			'taxonomy'	=> $taxonomy,
			'slug'		=> $slug
		);
	}
	return false;
}

/**
*  acf_get_encoded_terms
*
*  Returns an array of WP_Term objects from an array of encoded strings
*
*  @date	9/9/18
*  @since	5.7.5
*
*  @param	array $values The array of encoded strings.
*  @return	array
*/
function acf_get_encoded_terms( $values ) {
	
	// vars
	$terms = array();
	
	// loop over values
	foreach( (array) $values as $value ) {
		
		// find term from string
		$term = acf_get_term( $value );
		
		// append
		if( $term instanceof WP_Term ) {
			$terms[] = $term;
		}
	}
	
	// return
	return $terms;
}

/**
*  acf_get_choices_from_terms
*
*  Returns an array of choices from the terms provided.
*
*  @date	8/9/18
*  @since	5.7.5
*
*  @param	array $values and array of WP_Terms objects or encoded strings.
*  @param	string $format The value format (term_id, slug).
*  @return	array
*/
function acf_get_choices_from_terms( $terms, $format = 'term_id' ) {
	
	// vars
	$groups = array();
	
	// get taxonomy lables
	$labels = acf_get_taxonomy_labels();
	
	// convert array of encoded strings to terms
	$term = reset($terms);
	if( !$term instanceof WP_Term ) {
		$terms = acf_get_encoded_terms( $terms );
	}
	
	// loop over terms
	foreach( $terms as $term ) {
		$group = $labels[ $term->taxonomy ];
		$choice = acf_get_choice_from_term( $term, $format );
		$groups[ $group ][ $choice['id'] ] = $choice['text'];
	}
	
	// return
	return $groups;
}

/**
*  acf_get_choices_from_grouped_terms
*
*  Returns an array of choices from the grouped terms provided.
*
*  @date	8/9/18
*  @since	5.7.5
*
*  @param	array $value A grouped array of WP_Terms objects.
*  @param	string $format The value format (term_id, slug).
*  @return	array
*/
function acf_get_choices_from_grouped_terms( $value, $format = 'term_id' ) {
	
	// vars
	$groups = array();
	
	// loop over values
	foreach( $value as $group => $terms ) {
		$groups[ $group ] = array();
		foreach( $terms as $term_id => $term ) {
			$choice = acf_get_choice_from_term( $term, $format );
			$groups[ $group ][ $choice['id'] ] = $choice['text'];
		}
	}
	
	// return
	return $groups;
}

/**
*  acf_get_choice_from_term
*
*  Returns an array containing the id and text for this item.
*
*  @date	10/9/18
*  @since	5.7.6
*
*  @param	object $item The item object such as WP_Post or WP_Term.
*  @param	string $format The value format (term_id, slug)
*  @return	array
*/
function acf_get_choice_from_term( $term, $format = 'term_id' ) {
	
	// vars
	$id = $term->term_id;
	$text = acf_get_term_title( $term );
	
	// return format
	if( $format == 'slug' ) {
		$id = acf_encode_term($term);
	}
	
	// return
	return array(
		'id'	=> $id,
		'text'	=> $text
	);
}



?>