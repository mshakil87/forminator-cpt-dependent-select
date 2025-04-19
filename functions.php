<?php

/**
 * Your code goes below.
 */

 add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_script(
        'dynamic-select-fields',
        get_stylesheet_directory_uri() . '/js/dynamic-select-fields.js', // Path to your JS file
        array( 'jquery' ),
        '1.0',
        true
    );

    // Pass AJAX URL to the script
    wp_localize_script( 'dynamic-select-fields', 'ajax_object', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
    ) );
} );


/**
 * AJAX handler for fetching custom post type -> models based on the selected Custome post type -> make
 * I am getting title of the selected make and then fetching the models based on that title.
 * The models are custom post type and the make is a custom post type. The make is a parent of the model.
 * You can replace title with ID if you want to get the models based on the ID of the make.
 */

add_action( 'wp_ajax_get_models_by_make', 'get_models_by_make' );
add_action( 'wp_ajax_nopriv_get_models_by_make', 'get_models_by_make' );

function get_models_by_make() {
    // Get the selected make title from the AJAX request
    $make = isset( $_POST['make'] ) ? sanitize_text_field( $_POST['make'] ) : '';
    if ( empty( $make ) ) {
        wp_send_json_error( 'Invalid make value' );
    }

    // Fetch the make post by title
    $make_post = get_posts( array(
        'post_type'      =>  'make', // Replace with your custom post type for makes
        'title'          => $make,
        'posts_per_page' => 1,
        'post_status'    => 'publish',
    ) );

    if ( empty( $make_post ) ) {
        wp_send_json_error( 'Make not found' );
    }
	
    $make_id = $make_post[0]->ID; // Get the ID of the make post
	$args = array(
		'post_type'         => 'model',
		'post_status'       => 'publish',
		'orderby'           => 'title',
		'order'             => 'ASC',
		'meta_query'    => array(
			'relation' => 'OR',
			array(
				'key'       => 'make_reference',
				'value'     => $make_id, // the ID of the member
				'compare'   => 'LIKE',
			)
		)
	);
	$models = get_posts($args);
    if ( empty( $models ) ) {
        wp_send_json_error( 'No model found' );
    }

    // Prepare the response data
    $options = array();
    foreach ( $models as $model ) {
        $options[] = array(
            'value' => $model->post_title,
            'label' => $model->post_title,
        );
    }

    wp_send_json_success( $options );
}