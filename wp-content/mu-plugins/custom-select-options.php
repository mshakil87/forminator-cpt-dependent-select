<?php

/*
* Plugin Name: Custom Select Options
* Description: Dynamically populate select field options in Forminator forms based on custom post types.
* Version: 1.0
* Author: Mobeen Shakil
* Author URI: https://www.mobeenshakil.com
*/

/*
* This code snippet dynamically populates select field options in Forminator forms based on custom post types.
* It uses the Forminator API to fetch posts from specified post types and set them as options for select fields in a specific form.
* The code also includes a filter to replace the form data with the selected values from the select fields.
* The form ID and select field IDs are hardcoded in the snippet, so you may need to change them according to your requirements.
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
add_filter(
	'forminator_cform_render_fields',
	function( $wrappers, $model_id ) {
		if( $model_id != 63 ){ // Form ID for the custom select options, change as needed
			return $wrappers;
		}
		$select_fields_data = array(
			'select-1' => 'make', // Change 'make' to the actual post type slug for the first select field
			'select-2' => 'model', // Change 'model' to the actual post type slug for the second select field
			'select-3' => 'model_year', // Change 'model_year' to the actual post type slug for the third select field
		);
		/**
		 * You can add more select fields and their corresponding post types here.
		 * 'select-4' => 'post_type_slug',
		 */

		foreach ( $wrappers as $wrapper_key => $wrapper ) {
			/*
			* Check if the wrapper has fields and if the first field is a select field.
			* You can modify this condition to check for other field types as needed.
			*/
			if ( ! isset( $wrapper[ 'fields' ] ) ) {
				continue;
			}
			/**
			 * * Check if the first field is a select field and if it has options.
			 * * You can modify this condition to check for other field types as needed.
			 */
			if ( 
				isset( $select_fields_data[ $wrapper[ 'fields' ][ 0 ][ 'element_id' ] ] ) &&
				! empty( $select_fields_data[ $wrapper[ 'fields' ][ 0 ][ 'element_id' ] ] )
			) {
				// Get the posts for the selected post type
				// You can modify the query to fetch posts based on your requirements.
				$posts = get_posts( array( 'post_type' => $select_fields_data[ $wrapper[ 'fields' ][ 0 ][ 'element_id' ] ] ) );
				if ( ! empty( $posts ) ) {
					$new_options = array();
					$opt_data = array();
					foreach( $posts as $post ) {
						/*
						* You can modify the label and value for the options as needed.
						* For example, you can use the post title, ID, or any custom field value.
						* In this case, we are using the post title as both the label and value.
						* You can also add a limit if needed.
						* For example, you can set a limit of 10 characters for the label.
						* You can not add any data-attribute to the select field options.
						* For example, you can add a data-attribute for the post ID or any custom field value.
						*/
						$new_options[] = array(
							'label' => $post->post_title,
							'value' => $post->post_title,
							'limit' => '',
							'key'   => forminator_unique_key(),
						);
						$opt_data['options'] = $new_options;
					}
					$select_field = Forminator_API::get_form_field( $model_id, $wrapper['fields'][0]['element_id'], true );
					if( $select_field ){
						if( $select_field['options'][0]['label'] != $opt_data['options'][0]['label'] ){
							Forminator_API::update_form_field( $model_id, $wrapper['fields'][0]['element_id'], $opt_data);
							$wrappers[ $wrapper_key ][ 'fields' ][ 0 ][ 'options' ] = $new_options;
						}
					}
				}
			}
		}
		return $wrappers;
	},
	10,
	2
);

/*
** This filter replaces the form data with the selected values from the select fields.
* It checks if the form ID matches the specified form ID and if the content is empty.
*/
add_filter(
	'forminator_replace_form_data', 
	function( $content, $data, $fields ) {
		if( $data['form_id'] != 63 ){
			return $content;
		}

		if ( ! empty( $content ) ) {
			return $content;
		}

		$form_fields = Forminator_API::get_form_fields( $data['form_id'] );
		$data_field = '';
		foreach($data as $key => $value){
	    	if ( strpos( $key, 'select' ) !== false ) {
	    		$values = '';
	    		$field_value = isset( $data[ $key ] ) ? $data[ $key ] : null;

		    	if ( ! is_null( $field_value ) ) {
		    		$fields_slugs  = wp_list_pluck( $form_fields, 'slug' );
					$field_key     = array_search( $key, $fields_slugs, true );
					$field_options = false !== $field_key && ! empty( $form_fields[ $field_key ]->raw['options'] )
							? wp_list_pluck( $form_fields[ $field_key ]->options, 'label', 'value' )
							: array();

					if ( ! isset( $field_options[ $field_value ] ) && isset( $_POST[ $key ] ) ) {
						return sanitize_text_field( $_POST[ $key ] );
					}
				}
			}
		}
	    return $content;
	},
	10,
	3
);