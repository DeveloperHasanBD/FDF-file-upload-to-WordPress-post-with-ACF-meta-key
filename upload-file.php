<?php

/**
 * template name: Image upload
 */

 
require_once(ABSPATH . 'wp-admin/includes/media.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');


// URL of the PDF file you want to upload
$pdf_url = 'https://casa.rezz.ch/sap/AllegatiArticoli/113309_CUTTER LAMA FISSA 19 MM..pdf';

// ID of the post where you want to attach the file
$post_id = 1;

// Set the desired file name
$file_name = '113309_CUTTER LAMA FISSA 19 MM.pdf';

// Fetch the contents of the PDF file using WordPress HTTP API
$response = wp_remote_get($pdf_url);

// Check if the request was successful
if (!is_wp_error($response) && $response['response']['code'] === 200) {
    // Get the PDF content
    $pdf_content = wp_remote_retrieve_body($response);

    // Upload the file to WordPress media library
    $upload = wp_upload_bits($file_name, null, $pdf_content);

    // Check if upload was successful
    if (!$upload['error']) {
        // Get the uploaded file URL
        $file_url = $upload['url'];

        // Attach the file to the post
        $attachment_id = wp_insert_attachment(array(
            'guid'           => $file_url,
            'post_mime_type' => 'application/pdf',
            'post_title'     => preg_replace('/\.[^.]+$/', '', $file_name),
            'post_content'   => '',
            'post_status'    => 'inherit'
        ), $file_url, $post_id);

        $attachment_data = wp_generate_attachment_metadata($attachment_id, $file_url);
        wp_update_attachment_metadata($attachment_id, $attachment_data);

        // Update ACF meta field for the post
        update_field('upload_file', $attachment_id, $post_id);

        echo "PDF file uploaded successfully and attached to post ID: $post_id";
    } else {
        echo "Error uploading PDF file: " . $upload['error'];
    }
} else {
    echo "Error fetching PDF content from URL.";
}
