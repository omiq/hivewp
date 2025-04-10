<?php
/**
 * Plugin Name:       RSS Feed Importer
 * Plugin URI:        https://yourwebsite.com/rss-importer
 * Description:       Imports posts from a specified RSS feed on a schedule.
 * Version:           1.0.0
 * Author:            Your Name
 * Author URI:        https://yourwebsite.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       rss-importer
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Ensure core functions are loaded early
require_once( ABSPATH . WPINC . '/functions.php' );

define( 'RSS_IMPORTER_OPTION_GROUP', 'rss_importer' );
define( 'RSS_IMPORTER_OPTION_NAME', 'rss_importer_options' );
define( 'RSS_IMPORTER_CRON_HOOK', 'rss_importer_cron_hook' );

// Add options page
function rss_importer_options_page() {
    add_submenu_page(
        'tools.php',
        'RSS Importer Options',
        'RSS Importer',
        'manage_options',
        'rss-importer',
        'rss_importer_options_page_html'
    );
}
add_action('admin_menu', 'rss_importer_options_page');

// Register settings
function rss_importer_settings_init() {
    register_setting( RSS_IMPORTER_OPTION_GROUP, RSS_IMPORTER_OPTION_NAME );

    add_settings_section(
        'rss_importer_section_main',
        __( 'RSS Feed Settings', 'rss-importer' ),
        'rss_importer_section_main_callback',
        RSS_IMPORTER_OPTION_GROUP
    );

    add_settings_field(
        'rss_importer_field_url',
        __( 'Feed URL', 'rss-importer' ),
        'rss_importer_field_url_cb',
        RSS_IMPORTER_OPTION_GROUP,
        'rss_importer_section_main',
        [ 'label_for' => 'rss_importer_field_url' ]
    );

    add_settings_field(
        'rss_importer_field_schedule',
        __( 'Check Frequency', 'rss-importer' ),
        'rss_importer_field_schedule_cb',
        RSS_IMPORTER_OPTION_GROUP,
        'rss_importer_section_main',
        [ 'label_for' => 'rss_importer_field_schedule' ]
    );

    add_settings_field(
        'rss_importer_field_qty',
        __( 'Posts per Check', 'rss-importer' ),
        'rss_importer_field_qty_cb',
        RSS_IMPORTER_OPTION_GROUP,
        'rss_importer_section_main',
        [ 'label_for' => 'rss_importer_field_qty' ]
    );

    add_settings_field(
        'rss_importer_field_status',
        __( 'Import Status', 'rss-importer' ),
        'rss_importer_field_status_cb',
        RSS_IMPORTER_OPTION_GROUP,
        'rss_importer_section_main',
        [ 'label_for' => 'rss_importer_field_status' ]
    );
}
add_action( 'admin_init', 'rss_importer_settings_init' );

// Section callback
function rss_importer_section_main_callback( $args ) {
    ?>
    <p id="<?php echo esc_attr( $args['id'] ); ?>">
        <?php esc_html_e( 'Configure the RSS feed to import posts from.', 'rss-importer' ); ?>
         <?php esc_html_e( 'Import schedules rely on WP-Cron.', 'rss-importer' ); ?>
         <a href="https://developer.wordpress.org/plugins/cron/" target="_blank"><?php esc_html_e( 'Learn more about WP-Cron', 'rss-importer' ); ?></a>.
    </p>
    <?php
}

// Field callbacks
function rss_importer_get_option( $key, $default = '' ) {
    $options = get_option( RSS_IMPORTER_OPTION_NAME );
    return isset( $options[ $key ] ) ? $options[ $key ] : $default;
}

function rss_importer_field_url_cb( $args ) {
    $value = rss_importer_get_option( $args['label_for'] );
    ?>
    <input type="url" id="<?php echo esc_attr( $args['label_for'] ); ?>"
           name="<?php echo RSS_IMPORTER_OPTION_NAME; ?>[<?php echo esc_attr( $args['label_for'] ); ?>]"
           value="<?php echo esc_url( $value ); ?>"
           required="required"
           class="regular-text code">
    <p class="description">
        <?php esc_html_e( 'Enter the full URL of the RSS or Atom feed.', 'rss-importer' ); ?>
    </p>
    <?php
}

function rss_importer_field_schedule_cb( $args ) {
    $value = rss_importer_get_option( $args['label_for'], 'hourly' ); // Default to hourly
    $schedules = wp_get_schedules();
    ?>
    <select id="<?php echo esc_attr( $args['label_for'] ); ?>"
            name="<?php echo RSS_IMPORTER_OPTION_NAME; ?>[<?php echo esc_attr( $args['label_for'] ); ?>]">
        <?php foreach ( $schedules as $name => $details ) : ?>
            <option value="<?php echo esc_attr( $name ); ?>" <?php selected( $value, $name ); ?>>
                <?php echo esc_html( $details['display'] ); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <p class="description">
        <?php esc_html_e( 'How often WordPress should check the feed for new posts.', 'rss-importer' ); ?>
    </p>
    <?php
}

function rss_importer_field_qty_cb( $args ) {
    $value = rss_importer_get_option( $args['label_for'], 10 ); // Default to 10
    ?>
    <input type="number" id="<?php echo esc_attr( $args['label_for'] ); ?>"
           name="<?php echo RSS_IMPORTER_OPTION_NAME; ?>[<?php echo esc_attr( $args['label_for'] ); ?>]"
           value="<?php echo esc_attr( $value ); ?>"
           min="1" max="100" step="1" class="small-text">
    <p class="description">
        <?php esc_html_e( 'Maximum number of posts to import per check (1-100).', 'rss-importer' ); ?>
    </p>
    <?php
}

function rss_importer_field_status_cb( $args ) {
    $value = rss_importer_get_option( $args['label_for'], 'draft' ); // Default to draft
    ?>
    <select id="<?php echo esc_attr( $args['label_for'] ); ?>"
            name="<?php echo RSS_IMPORTER_OPTION_NAME; ?>[<?php echo esc_attr( $args['label_for'] ); ?>]">
        <option value="draft" <?php selected( $value, 'draft' ); ?>>
            <?php esc_html_e( 'Draft', 'rss-importer' ); ?>
        </option>
        <option value="publish" <?php selected( $value, 'publish' ); ?>>
            <?php esc_html_e( 'Published', 'rss-importer' ); ?>
        </option>
         <option value="pending" <?php selected( $value, 'pending' ); ?>>
            <?php esc_html_e( 'Pending Review', 'rss-importer' ); ?>
        </option>
         <option value="private" <?php selected( $value, 'private' ); ?>>
            <?php esc_html_e( 'Private', 'rss-importer' ); ?>
        </option>
    </select>
    <p class="description">
        <?php esc_html_e( 'Status for newly imported posts.', 'rss-importer' ); ?>
    </p>
    <?php
}

// Options page HTML
function rss_importer_options_page_html() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Display persistent update messages
    settings_errors( 'rss_importer_messages' );
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields( RSS_IMPORTER_OPTION_GROUP );
            do_settings_sections( RSS_IMPORTER_OPTION_GROUP );
            submit_button( 'Save Settings' );
            ?>
        </form>
    </div>
    <?php
}

/**
 * Handle rescheduling the cron job when the plugin options are updated.
 *
 * @param mixed $old_value The old option value.
 * @param mixed $new_value The new option value.
 */
function rss_importer_handle_options_update( $old_value, $new_value ) {
    // Remove debug logs
    /*
    if ( ! function_exists( 'wp_validate_url' ) ) {
        error_log( 'RSS Importer Debug: wp_validate_url() does NOT exist in rss_importer_handle_options_update.' );
    } else {
        error_log( 'RSS Importer Debug: wp_validate_url() DOES exist in rss_importer_handle_options_update.' );
    }
    */

    $schedule = isset($new_value['rss_importer_field_schedule']) ? $new_value['rss_importer_field_schedule'] : 'hourly';
    $feed_url = isset($new_value['rss_importer_field_url']) ? trim($new_value['rss_importer_field_url']) : '';

    // Clear any existing schedule for this hook
    wp_clear_scheduled_hook( RSS_IMPORTER_CRON_HOOK );

    // Ensure functions.php is loaded before calling wp_validate_url
    // require_once( ABSPATH . WPINC . '/functions.php' ); // Removed as it didn't solve the issue

    // Check if the URL is valid (using esc_url_raw as alternative) before scheduling
    if ( ! empty( $feed_url ) && ! empty( esc_url_raw( $feed_url ) ) ) {
        wp_schedule_event( time(), $schedule, RSS_IMPORTER_CRON_HOOK );
        // Add a persistent admin notice for success
        add_settings_error(
            'rss_importer_messages', // Use the same slug as settings_errors()
            'rss_importer_schedule_ok',
            __( 'Feed check scheduled.', 'rss-importer' ),
            'updated' // 'updated' or 'success'
        );
    } else {
        // Add a persistent admin notice for failure
        add_settings_error(
            'rss_importer_messages',
            'rss_importer_schedule_fail',
            __( 'Feed check NOT scheduled (invalid or empty URL).', 'rss-importer' ),
            'error'
        );
    }
}
add_action( 'update_option_' . RSS_IMPORTER_OPTION_NAME, 'rss_importer_handle_options_update', 10, 2 );

// Include necessary files for image handling and feed fetching
require_once( ABSPATH . 'wp-admin/includes/media.php' );
require_once( ABSPATH . 'wp-admin/includes/file.php' );
require_once( ABSPATH . 'wp-admin/includes/image.php' );
require_once( ABSPATH . 'wp-includes/feed.php' );

/**
 * Downloads an image from a URL, saves it to the media library, and returns the new URL.
 *
 * @param string $image_url The URL of the image to download.
 * @param int    $post_id   The ID of the post this image is being attached to.
 * @return string|false The new URL of the uploaded image, or false on failure.
 */
function rss_importer_upload_image( $image_url, $post_id = 0 ) {
    // Increase timeout for potentially large images
    $timeout_seconds = 30;
    // Download image to temp file
    $temp_file = download_url( $image_url, $timeout_seconds );

    if ( is_wp_error( $temp_file ) ) {
        error_log( 'RSS Importer Error: Failed to download image ' . $image_url . ' - ' . $temp_file->get_error_message() );
        return false;
    }

    // Get file details
    $file_name = basename( parse_url( $image_url, PHP_URL_PATH ) );
    if ( ! $file_name ) {
        $file_name = 'rss-imported-image-' . time(); // Fallback name
    }
    // $file_type = wp_check_filetype( $file_name, null ); // Not needed for media_handle_sideload
 
     // Generate a unique filename based on URL hash + original extension
     $path_parts = pathinfo($file_name);
     $extension = isset($path_parts['extension']) ? $path_parts['extension'] : '';
     $unique_filename = $file_name; // Use original filename
 
    // Prepare file array for media_handle_sideload
    $file_array = [
        'name'     => $unique_filename, // The desired filename
        'tmp_name' => $temp_file      // Path to the temporary file downloaded by download_url
    ];
 
    // Check if the required function exists.
    if ( ! function_exists( 'media_handle_sideload' ) ) {
        @unlink( $temp_file );
        error_log('RSS Importer Error: media_handle_sideload function does not exist!');
        return false;
    }
 
    // Let WordPress handle the sideloading, attachment creation, and metadata generation.
    // Pass $post_id to associate the attachment with the post.
    $attachment_id = media_handle_sideload( $file_array, $post_id );
 
    // Check for errors
    if ( is_wp_error( $attachment_id ) ) {
        @unlink( $temp_file ); // Delete temp file
        error_log( 'RSS Importer Error: media_handle_sideload failed - ' . $attachment_id->get_error_message() );
        return false;
    } else {
        error_log( 'RSS Importer Debug: media_handle_sideload successful. Attachment ID: ' . $attachment_id );
        // No need for separate metadata generation, media_handle_sideload does it.
    }
    // Return the attachment ID on success
    return $attachment_id;
}

/**
 * Processes the content to find images, upload them, and replace URLs.
 *
 * @param string $content The HTML content to process.
 * @return string The processed content with updated image URLs.
 */
function rss_importer_process_content_images( $content ) {
    if ( empty($content) || ! function_exists('mb_convert_encoding') ) {
         // If no content or mbstring not available, return original
        return $content;
    }

    // Use DOMDocument to reliably parse HTML
    // Suppress errors due to potentially invalid HTML in feeds
    $doc = new DOMDocument();
    // Ensure UTF-8 encoding is handled correctly
    @$doc->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

    $images = $doc->getElementsByTagName('img');
    $processed_urls = []; // Avoid processing the same URL multiple times

    foreach ( $images as $img ) {
        $original_src = $img->getAttribute('src');

        // Skip empty URLs, data URIs, or already processed URLs
        if ( empty( $original_src ) || strpos( $original_src, 'data:' ) === 0 || isset( $processed_urls[ $original_src ] ) ) {
            continue;
        }

        // Attempt to upload the image
        $new_src = rss_importer_upload_image( $original_src );

        if ( $new_src ) {
            // If upload successful, update the src attribute
            $img->setAttribute( 'src', $new_src );
            $processed_urls[ $original_src ] = $new_src;
        } else {
            // Mark as processed even if failed to prevent retries
            $processed_urls[ $original_src ] = false;
        }
    }

    // Return the modified HTML
    // Use saveHTML() which defaults to UTF-8
    return $doc->saveHTML();
}

/**
 * Check if a post with the given GUID already exists.
 *
 * @param string $guid The feed item GUID.
 * @return int|false Post ID if found, false otherwise.
 */
function rss_importer_post_exists( $guid ) {
    $args = [
        'post_type'   => 'post',
        'post_status' => 'any', // Check against all statuses
        'meta_query'  => [
            [
                'key'     => '_rss_importer_guid',
                'value'   => $guid,
                'compare' => '=',
            ],
        ],
        'posts_per_page' => 1,
        'fields'         => 'ids',
    ];
    $query = new WP_Query( $args );
    return $query->have_posts() ? $query->posts[0] : false;
}

/**
 * Main cron function to fetch and import posts.
 */
function rss_importer_cron() {
    global $wpdb; // Make the database object available

    $options   = get_option( RSS_IMPORTER_OPTION_NAME );
    $feed_url  = isset( $options['rss_importer_field_url'] ) ? trim( $options['rss_importer_field_url'] ) : '';
    $post_qty  = isset( $options['rss_importer_field_qty'] ) ? absint( $options['rss_importer_field_qty'] ) : 10;
    $post_status = isset( $options['rss_importer_field_status'] ) ? sanitize_key( $options['rss_importer_field_status'] ) : 'draft';

    // Validate quantity
    if ( $post_qty < 1 || $post_qty > 100 ) {
        $post_qty = 10;
    }

    // Validate post status
    $allowed_statuses = [ 'draft', 'publish', 'pending', 'private' ];
    if ( ! in_array( $post_status, $allowed_statuses, true ) ) {
        $post_status = 'draft';
    }

    // Replace wp_validate_url with esc_url_raw check for cron context
    if ( empty( $feed_url ) || empty( esc_url_raw( $feed_url ) ) ) {
        error_log( 'RSS Importer Error: Cron aborted - Invalid or empty feed URL provided: ' . $feed_url );
        return; // Don't run if URL is invalid
    }

    // Fetch the feed
    $feed = fetch_feed( $feed_url );

    if ( is_wp_error( $feed ) ) {
        error_log( 'RSS Importer Error: Failed to fetch feed ' . $feed_url . ' - ' . $feed->get_error_message() );
        return; // Exit if feed fetch fails
    }

    // Get the feed items
    $max_items = $feed->get_item_quantity( $post_qty ); // Respect the user's limit
    $feed_items = $feed->get_items( 0, $max_items );

    if ( empty( $feed_items ) ) {
        // error_log('RSS Importer: No new items found in feed ' . $feed_url);
        return; // No items to process
    }

    $imported_count = 0;
    $last_attachment_id_created = null; // Track the last attachment ID

    // Loop through items
    foreach ( $feed_items as $item ) {
        $guid = $item->get_id(); // Use get_id() for GUID
        if ( ! $guid ) {
            $guid = $item->get_permalink(); // Fallback to permalink if no GUID
        }
        if ( ! $guid ) {
            error_log( 'RSS Importer Warning: Skipping item with no GUID or permalink.' );
            continue; // Skip if no unique identifier
        }

        // Check if post already exists
        if ( rss_importer_post_exists( $guid ) ) {
            // error_log('RSS Importer: Skipping already imported item with GUID ' . $guid);
            continue;
        }

        // Process content for images
        $content = $item->get_content();
        $processed_content = rss_importer_process_content_images( $content );

        // Prepare post data
        $post_data = [
            'post_title'   => wp_strip_all_tags( $item->get_title() ),
            'post_content' => wp_kses_post( $processed_content ), // Sanitize content
            'post_status'  => $post_status,
            'post_author'  => 1, // TODO: Make author configurable?
            'post_date'    => $item->get_date( 'Y-m-d H:i:s' ), // Use feed item date
            'post_date_gmt' => $item->get_date( 'Y-m-d H:i:s', true ), // Use GMT date if available
            'meta_input'   => [
                '_rss_importer_guid' => $guid,
                '_rss_importer_feed_url' => $feed_url,
                '_rss_importer_original_link' => $item->get_permalink(),
            ],
          ];

        // Insert the post
        $post_id = wp_insert_post( $post_data, true ); // Pass true to return WP_Error on failure

        if ( is_wp_error( $post_id ) ) {
            error_log( 'RSS Importer Error: Failed to insert post - ' . $post_id->get_error_message() );
        } else {
            // error_log('RSS Importer: Successfully imported post ID ' . $post_id . ' from GUID ' . $guid);
            $imported_count++;

            // Check for image enclosure to set as featured image
            $enclosure = $item->get_enclosure();
            if ( $enclosure && $enclosure->get_link() && strpos( $enclosure->get_type(), 'image' ) === 0 ) {
                $image_url = $enclosure->get_link();
                error_log('RSS Importer Debug: Found image enclosure. Type: ' . $enclosure->get_type() . ', URL: ' . $image_url);

                $attachment_id = rss_importer_upload_image( $image_url, $post_id ); // Returns ID or false

                

                error_log('Attachment ID: ' . $attachment_id);

                error_log('RSS Importer Debug: rss_importer_upload_image returned ID: ' . ($attachment_id ? $attachment_id : 'false'));

                if ( $attachment_id ) {
                    // Check if metadata actually exists for the attachment before setting
                    $last_attachment_id_created = $attachment_id; // Store the latest ID

                    $attachment_meta = get_post_meta( $attachment_id, '_wp_attachment_metadata', true );
                    error_log('RSS Importer Debug: Metadata check for attachment ID ' . $attachment_id . ': ' . ( empty($attachment_meta) ? 'MISSING' : 'FOUND' ) );

                    error_log('RSS Importer Debug: PRE-SET CHECK - Post ID: ' . $post_id . ', Attachment ID: ' . $attachment_id);

                    $set_result = set_post_thumbnail( $post_id, $attachment_id );
                    error_log('RSS Importer Debug: Attempted set_post_thumbnail for post ' . $post_id . ' with attachment_id ' . $attachment_id . '. Result: ' . var_export($set_result, true));
                } // rss_importer_upload_image handles its own errors
            } else if ($enclosure) {
                // Log if enclosure exists but isn't an image or lacks a link
                error_log('RSS Importer Debug: Found enclosure, but not a valid image type or link. Type: ' . $enclosure->get_type() . ', URL: ' . $enclosure->get_link());
            }
        }
    }

    // Final check: Does the last created attachment still exist?
    if ( $last_attachment_id_created ) {
        $final_check_post = get_post( $last_attachment_id_created );
        if ( $final_check_post && $final_check_post->post_type === 'attachment' ) {
            error_log('RSS Importer Debug: End-of-cron check: Attachment ID ' . $last_attachment_id_created . ' still exists.');
        } else {
            error_log('RSS Importer CRITICAL: End-of-cron check: Attachment ID ' . $last_attachment_id_created . ' appears to be MISSING or not an attachment!');
        }
    }
}

// Hook the main function into our custom cron schedule
add_action( RSS_IMPORTER_CRON_HOOK, 'rss_importer_cron' );

/**
 * Schedule the cron job on plugin activation.
 */
function rss_importer_activate() {
    $options = get_option( RSS_IMPORTER_OPTION_NAME );
    $schedule = isset($options['rss_importer_field_schedule']) ? $options['rss_importer_field_schedule'] : 'hourly';
    $feed_url = isset($options['rss_importer_field_url']) ? trim($options['rss_importer_field_url']) : '';

    // Only schedule if not already scheduled and feed URL is valid
    if ( ! wp_next_scheduled( RSS_IMPORTER_CRON_HOOK ) && ! empty( $feed_url ) && wp_validate_url( $feed_url ) ) {
        wp_schedule_event( time(), $schedule, RSS_IMPORTER_CRON_HOOK );
    }
}
register_activation_hook( __FILE__, 'rss_importer_activate' );

/**
 * Clear the cron job on plugin deactivation.
 */
function rss_importer_deactivate() {
    wp_clear_scheduled_hook( RSS_IMPORTER_CRON_HOOK );
}
register_deactivation_hook( __FILE__, 'rss_importer_deactivate' );

// TODO: Add function rss_importer_add_cron_interval() if custom intervals needed (WP default might suffice)

// /**
//  * Force WordPress to use GD library instead of Imagick for testing.
//  *
//  * @param array $editors Array of available image editor class names.
//  * @return array Filtered array of image editor class names.
//  */
// function rss_importer_force_gd_editor( $editors ) {
//     // Remove Imagick editor if it exists
//     $editors = array_diff( $editors, [ 'WP_Image_Editor_Imagick' ] );
//     // Ensure GD editor is present (it usually is by default)
//     if ( ! in_array( 'WP_Image_Editor_GD', $editors ) ) {
//         // This is unlikely, but just in case GD wasn't listed initially
//         // Note: This doesn't guarantee GD *will* work if it's not properly configured
//     }
// 	// Optionally log which editors are remaining
// 	// error_log('RSS Importer Debug: Available Image Editors: ' . print_r($editors, true));
//     return $editors;
// }
// add_filter( 'wp_image_editors', 'rss_importer_force_gd_editor' );

?>