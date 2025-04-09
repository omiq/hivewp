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

    if ( isset( $_GET['settings-updated'] ) ) {
        add_settings_error( 'rss_importer_messages', 'rss_importer_message', __( 'Settings Saved', 'rss-importer' ), 'updated' );

        // Reschedule cron job
        $options = get_option( RSS_IMPORTER_OPTION_NAME );
        $schedule = isset($options['rss_importer_field_schedule']) ? $options['rss_importer_field_schedule'] : 'hourly';
        $feed_url = isset($options['rss_importer_field_url']) ? trim($options['rss_importer_field_url']) : '';

        wp_clear_scheduled_hook( RSS_IMPORTER_CRON_HOOK );
        if ( !empty($feed_url) && wp_validate_url($feed_url) ) {
             wp_schedule_event( time(), $schedule, RSS_IMPORTER_CRON_HOOK );
             add_settings_error( 'rss_importer_messages', 'rss_importer_schedule_ok', __( 'Feed check scheduled.', 'rss-importer' ), 'updated' );
        } else {
             add_settings_error( 'rss_importer_messages', 'rss_importer_schedule_fail', __( 'Feed check NOT scheduled (invalid or empty URL).', 'rss-importer' ), 'error' );
        }
    }

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

// Include necessary files for image handling and feed fetching
require_once( ABSPATH . 'wp-admin/includes/media.php' );
require_once( ABSPATH . 'wp-admin/includes/file.php' );
require_once( ABSPATH . 'wp-admin/includes/image.php' );
require_once( ABSPATH . 'wp-includes/feed.php' );

/**
 * Downloads an image from a URL, saves it to the media library, and returns the new URL.
 *
 * @param string $image_url The URL of the image to download.
 * @return string|false The new URL of the uploaded image, or false on failure.
 */
function rss_importer_upload_image( $image_url ) {
    // Download image to temp file
    $temp_file = download_url( $image_url );

    if ( is_wp_error( $temp_file ) ) {
        error_log( 'RSS Importer Error: Failed to download image ' . $image_url . ' - ' . $temp_file->get_error_message() );
        return false;
    }

    // Get file details
    $file_name = basename( parse_url( $image_url, PHP_URL_PATH ) );
    if ( ! $file_name ) {
        $file_name = 'rss-imported-image-' . time(); // Fallback name
    }
    $file_type = wp_check_filetype( $file_name, null );

    // Prepare arguments for sideloading
    $file_data = [
        'name'     => $file_name,
        'type'     => $file_type['type'],
        'tmp_name' => $temp_file,
        'error'    => 0,
        'size'     => filesize( $temp_file ),
    ];

    $overrides = [
        'test_form' => false,
        'test_type' => false, // Allow all standard image types
    ];

    // Move the temporary file into the uploads directory
    $sideload = wp_handle_sideload( $file_data, $overrides );

    if ( isset( $sideload['error'] ) ) {
        @unlink( $temp_file ); // Delete temp file
        error_log( 'RSS Importer Error: Sideload failed - ' . $sideload['error'] );
        return false;
    }

    // Prepare attachment data
    $attachment = [
        'guid'           => $sideload['url'],
        'post_mime_type' => $sideload['type'],
        'post_title'     => preg_replace( '/\.[^.]+$/', '', $file_name ), // Title without extension
        'post_content'   => '',
        'post_status'    => 'inherit',
    ];

    // Insert the attachment
    $attachment_id = wp_insert_attachment( $attachment, $sideload['file'] );

    if ( is_wp_error( $attachment_id ) ) {
        @unlink( $sideload['file'] ); // Delete sideloaded file
        error_log( 'RSS Importer Error: Failed to insert attachment - ' . $attachment_id->get_error_message() );
        return false;
    }

    // Generate attachment metadata and update the database
    $attachment_data = wp_generate_attachment_metadata( $attachment_id, $sideload['file'] );
    wp_update_attachment_metadata( $attachment_id, $attachment_data );

    // error_log('RSS Importer: Successfully uploaded image ' . $sideload['url']);
    return $sideload['url'];
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

    if ( empty( $feed_url ) || ! wp_validate_url( $feed_url ) ) {
        error_log( 'RSS Importer Error: Cron aborted - Invalid or empty feed URL.' );
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
             // TODO: Add support for categories/tags based on feed data?
             // 'post_category' => [],\n             // 'tags_input' => $item->get_categories(), // Needs processing\n        ];\n\n        // Insert the post\n        $post_id = wp_insert_post( $post_data, true ); // Pass true to return WP_Error on failure\n\n        if ( is_wp_error( $post_id ) ) {\n            error_log( 'RSS Importer Error: Failed to insert post - ' . $post_id->get_error_message() );\n        } else {\n             // error_log('RSS Importer: Successfully imported post ID ' . $post_id . ' from GUID ' . $guid);\n            $imported_count++;\n        }\n    }\n\n    // Optional: Log summary\n    // if ($imported_count > 0) {\n    //     error_log('RSS Importer: Cron run finished. Imported ' . $imported_count . ' posts from ' . $feed_url);\n    // }\n}\n\n// Hook the main function into our custom cron schedule\nadd_action( RSS_IMPORTER_CRON_HOOK, 'rss_importer_cron' );\n\n/**\n * Schedule the cron job on plugin activation.\n */\nfunction rss_importer_activate() {\n    $options = get_option( RSS_IMPORTER_OPTION_NAME );\n    $schedule = isset($options['rss_importer_field_schedule']) ? $options['rss_importer_field_schedule'] : 'hourly';\n    $feed_url = isset($options['rss_importer_field_url']) ? trim($options['rss_importer_field_url']) : '';\n\n    // Only schedule if not already scheduled and feed URL is valid\n    if ( ! wp_next_scheduled( RSS_IMPORTER_CRON_HOOK ) && ! empty( $feed_url ) && wp_validate_url( $feed_url ) ) {\n        wp_schedule_event( time(), $schedule, RSS_IMPORTER_CRON_HOOK );\n    }\n}\nregister_activation_hook( __FILE__, 'rss_importer_activate' );\n\n/**\n * Clear the cron job on plugin deactivation.\n */\nfunction rss_importer_deactivate() {\n    wp_clear_scheduled_hook( RSS_IMPORTER_CRON_HOOK );\n}\nregister_deactivation_hook( __FILE__, 'rss_importer_deactivate' );\n\n// TODO: Add function rss_importer_add_cron_interval() if custom intervals needed (WP default might suffice)\n\n?>