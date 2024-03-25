<?php

/**
 * template name: Image upload
 */


require_once(ABSPATH . 'wp-admin/includes/media.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');




// URLs of the PDF files you want to upload
$pdf_urls = array(
    'https://casa.rezz.ch/sap/AllegatiArticoli/113310_N.10 LAME CUTTER SPEZZ.MM.18.pdf',
    'https://casa.rezz.ch/sap/AllegatiArticoli/113309_CUTTER LAMA FISSA 19 MM..pdf',
    'https://casa.rezz.ch/sap/AllegatiArticoli/113308_CUTTER ABS 18 MM. LAMA SPEZZ..pdf'
);

// ID of the post where you want to attach the files
$post_id = 1;

// Initialize an array to store attachment URLs
$uploaded_urls = array();

// Initialize an array to store attachment IDs
$attachment_ids = array();

// Loop through each PDF URL
foreach ($pdf_urls as $pdf_url) {
    // Check if the URL has already been uploaded
    if (in_array($pdf_url, $uploaded_urls)) {
        echo "File URL '$pdf_url' has already been uploaded.<br>";
        continue; // Skip to the next PDF URL
    }

    // Set the desired file name
    $file_name = basename($pdf_url);

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

            // Add the URL to the list of uploaded URLs
            $uploaded_urls[] = $pdf_url;

            // Attach the file to the post
            $attachment_id = wp_insert_attachment(array(
                'guid'           => $file_url,
                'post_mime_type' => 'application/pdf',
                'post_title'     => preg_replace('/\.[^.]+$/', '', $file_name),
                'post_content'   => '',
                'post_status'    => 'inherit'
            ), $file_url, $post_id);

            // Update attachment metadata
            // require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attachment_data = wp_generate_attachment_metadata($attachment_id, $file_url);
            wp_update_attachment_metadata($attachment_id, $attachment_data);

            // Add the attachment ID to the array
            $attachment_ids[] = $attachment_id;

            echo "File URL '$pdf_url' uploaded successfully.<br>";
        } else {
            echo "Error uploading PDF file '$pdf_url': " . $upload['error'] . "<br>";
        }
    } else {
        echo "Error fetching PDF content from URL: $pdf_url<br>";
    }
}

// Update ACF repeater field for the post
update_field('multiple_file_upload', $attachment_ids, $post_id);
echo '<pre>';
print_r($attachment_ids);
echo '</pre>';
echo "PDF files uploaded successfully and attached to post ID: $post_id";
