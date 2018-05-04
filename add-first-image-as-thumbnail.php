<?php
/*
Plugin Name: Add first post image as a featured image
Version: 1.0
Plugin URI: http://zoompcreative.com
Description: This plugin searches for the first image within the contents of your blog post, uploads it to your WordPress installations and assigns it as a featured image. Use the bulk actions on the posts screen to assign thumbnails to selected posts.
Author: Felipe Rinaldi
Author URI: https://feliperinaldi.com
*/



add_filter( 'bulk_actions-edit-post', 'afpi_register_my_bulk_actions' );

function afpi_register_my_bulk_actions( $bulk_actions ) {
  $bulk_actions['assign_thumbnail'] = __( 'Assign first image as featured', 'afpi');
  return $bulk_actions;
}

add_filter( 'handle_bulk_actions-edit-post', 'afpi_bulk_action_handler', 10, 3 );
 
function afpi_bulk_action_handler( $redirect_to, $doaction, $post_ids ) {
  if ( $doaction !== 'assign_thumbnail' ) {
    return $redirect_to;
  }
  
  $count = 0;
  foreach ( $post_ids as $post_id ) {
    // Perform action for each post.
    if( afpi_assign_first_image_to_post($post_id) ) {
    	$count++;
    }
  
  }
  
  $redirect_to = add_query_arg( 'assigned_thumbs', $count, $redirect_to );
  return $redirect_to;
}

add_action( 'admin_notices', 'afpi_bulk_action_admin_notice' );
 
function afpi_bulk_action_admin_notice() {
  
  if ( ! empty( $_REQUEST['assigned_thumbs'] ) ) {
    $posts_count = intval( $_REQUEST['assigned_thumbs'] );
    printf( '<div id="message" class="updated fade">' .
      _n( 'Assigned featured image to %s post.',
        'Assigned featured images to %s posts.',
        $posts_count,
        'email_to_eric'
      ) . '</div>', $posts_count );
  }
}


function afpi_assign_first_image_to_post( $post_id ) {
	
	$attachment_id = '';
	$post = get_post( $post_id );

	preg_match( '@img(.*?)src="([^"]+)"@' , $post->post_content, $match );
	
	if( isset($match[2]) && false !== filter_var( $match[2], FILTER_VALIDATE_URL) ) {
		$attachment_id = afpi_handle_upload( $match[2], $post_id );
		return true;
	}
	
	return false;

}

function afpi_handle_upload( $url, $post_id ) {

	require_once(ABSPATH . "wp-admin" . '/includes/image.php');

	$filename 	= basename( $url );
	$photoData 	= file_get_contents( $url );

	$upload_file = wp_upload_bits( $filename, null, $photoData );
					
	if (!$upload_file['error']) {
		
		$wp_filetype = wp_check_filetype($filename, null );
		
		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_parent' => $post_id,
			'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
			'post_content' => '',
			'post_status' => 'inherit'
		);
		$attachment_id = wp_insert_attachment( $attachment, $upload_file['file'], $post_id );

		if ( ! is_wp_error( $attachment_id ) ) {
			
			$attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_file['file'] );
			
			wp_update_attachment_metadata( $attachment_id,  $attachment_data );

			set_post_thumbnail( $post_id, $attachment_id );

			return $attachment_id;
		
		} 
	}

	return false;
}
