<?php
/**
 *
 * @link              https://hivewp.com
 * @since             1.0.9
 * @package           Hivewp_cron
 *
 * @wordpress-plugin
 * Plugin Name:       HiveWP_cron
 * Plugin URI:        https://hivewp.com
 * Description:       Imports the author's blog posts from the Hive Blockchain
 * Version:           1.0.7
 * Author:            Chris Garrett
 * Author URI:        https://hivewp.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */





function hivewp_options_page()
{
    add_submenu_page(
        'tools.php',
        'HiveWP Options',
        'HiveWP Options',
        'manage_options',
        'HiveWP',
        'HiveWP_options_page_html'
    );
}
add_action('admin_menu', 'HiveWP_options_page');



/**
 * custom option and settings
 */
function HiveWP_settings_init() {
    // Register a new setting for "HiveWP" page.
    register_setting( 'HiveWP', 'HiveWP_options' );
 
    // Register a new section in the "HiveWP" page.
    add_settings_section(
        'HiveWP_section_developers',
        __( 'Hive WP Article Import Settings', 'HiveWP' ), 'HiveWP_section_developers_callback',
        'HiveWP'
    );
 
    // Register a new fields in the "HiveWP_section_developers" section, inside the "HiveWP" page.
    add_settings_field(
        'HiveWP_field_user', 
            __( 'User', 'HiveWP' ),
        'HiveWP_field_user_cb',
        'HiveWP',
        'HiveWP_section_developers',
        array(
            'label_for'         => 'HiveWP_field_user',
            'class'             => 'HiveWP_row',
            'HiveWP_custom_data' => 'custom',
        )
    );

    add_settings_field(
        'HiveWP_field_by', 
            __( 'By', 'HiveWP' ),
        'HiveWP_field_by_cb',
        'HiveWP',
        'HiveWP_section_developers',
        array(
            'label_for'         => 'HiveWP_field_by',
            'class'             => 'HiveWP_row',
            'HiveWP_custom_data' => 'custom',
        )
    );

    add_settings_field(
        'HiveWP_field_inc_tag', 
            __( 'Include Tagged', 'HiveWP' ),
        'HiveWP_field_inc_tag_cb',
        'HiveWP',
        'HiveWP_section_developers',
        array(
            'label_for'         => 'HiveWP_field_inc_tag',
            'class'             => 'HiveWP_row',
            'HiveWP_custom_data' => 'custom',
        )
    );


    add_settings_field(
        'HiveWP_field_ex_tag', 
            __( 'Exclude Tagged', 'HiveWP' ),
        'HiveWP_field_ex_tag_cb',
        'HiveWP',
        'HiveWP_section_developers',
        array(
            'label_for'         => 'HiveWP_field_ex_tag',
            'class'             => 'HiveWP_row',
            'HiveWP_custom_data' => 'custom',
        )
    );

    add_settings_field(
        'HiveWP_field_publish', 
            __( 'Publish', 'HiveWP' ),
        'HiveWP_field_publish_cb',
        'HiveWP',
        'HiveWP_section_developers',
        array(
            'label_for'         => 'HiveWP_field_publish',
            'class'             => 'HiveWP_row',
            'HiveWP_custom_data' => 'custom',
        )
    );


    add_settings_field(
        'HiveWP_field_schedule', 
            __( 'Schedule', 'HiveWP' ),
        'HiveWP_field_schedule_cb',
        'HiveWP',
        'HiveWP_section_developers',
        array(
            'label_for'         => 'HiveWP_field_schedule',
            'class'             => 'HiveWP_row',
            'HiveWP_custom_data' => 'custom',
        )
    );

    add_settings_field(
        'HiveWP_field_qty', 
            __( 'Quantity', 'HiveWP' ),
        'HiveWP_field_qty_cb',
        'HiveWP',
        'HiveWP_section_developers',
        array(
            'label_for'         => 'HiveWP_field_qty',
            'class'             => 'HiveWP_row',
            'HiveWP_custom_data' => 'custom',
        )
    );

}
 
/**
 * Register our HiveWP_settings_init to the admin_init action hook.
 */
add_action( 'admin_init', 'HiveWP_settings_init' );
 
 
/**
 * Custom option and settings:
 *  - callback functions
 */
 
 
/**
 * Developers section callback function.
 *
 * @param array $args  The settings array, defining title, id, callback.
 */
function HiveWP_section_developers_callback( $args ) {
    ?>
    <p id="<?php echo esc_attr( $args['id'] ); ?>">
    Settings in this admin page will allow you to set the behaviour of the Hive article import. Please note import schedules rely on <a href="https://developer.wordpress.org/plugins/cron/" target="_blank">WP Cron</a> functionality.</p>
    <?php
}
 
/**
 * user field callback
 */
function HiveWP_field_user_cb( $args ) {
    // Get the value of the setting we've registered with register_setting()
    $options = get_option( 'HiveWP_options' );
    ?>
          
             
    <input id="<?php echo esc_attr( $args['label_for'] ); ?>" type="text" 
    value="<?php echo isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : ( '' ); ?>"
        
    data-custom="<?php echo esc_attr( $args['HiveWP_custom_data'] ); ?>" name="HiveWP_options[<?php echo esc_attr( $args['label_for'] ); ?>]">

          
    
    <p class="description">
        <?php esc_html_e( 'Set the Hive user or community to pull posts from (without @)', 'HiveWP' ); ?>
    </p>


    <?php
}

/**
 * Inc Tag field callback
 */
function HiveWP_field_inc_tag_cb( $args ) {
    // Get the value of the setting we've registered with register_setting()
    $options = get_option( 'HiveWP_options' );
    ?>
          
             
    <input id="<?php echo esc_attr( $args['label_for'] ); ?>" type="text" 
    value="<?php echo isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : ( '' ); ?>"
        
    data-custom="<?php echo esc_attr( $args['HiveWP_custom_data'] ); ?>" name="HiveWP_options[<?php echo esc_attr( $args['label_for'] ); ?>]">

          
    
    <p class="description">
        <?php esc_html_e( 'Inlude posts with matching tag', 'HiveWP' ); ?>
    </p>


    <?php
}

/**
 * Ex Tag field callback
 */
function HiveWP_field_ex_tag_cb( $args ) {
    // Get the value of the setting we've registered with register_setting()
    $options = get_option( 'HiveWP_options' );
    ?>
          
             
    <input id="<?php echo esc_attr( $args['label_for'] ); ?>" type="text" 
    value="<?php echo isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : ( '' ); ?>"
        
    data-custom="<?php echo esc_attr( $args['HiveWP_custom_data'] ); ?>" name="HiveWP_options[<?php echo esc_attr( $args['label_for'] ); ?>]">

          
    
    <p class="description">
        <?php esc_html_e( 'Exclude posts with matching tag', 'HiveWP' ); ?>
    </p>


    <?php
}

/**
 * List by author or community callback
 */
function HiveWP_field_by_cb( $args ) {
    // Get the value of the setting we've registered with register_setting()
    $options = get_option( 'HiveWP_options' );
    ?>

    <select
            id="<?php echo esc_attr( $args['label_for'] ); ?>"
            data-custom="<?php echo esc_attr( $args['HiveWP_custom_data'] ); ?>"
            name="HiveWP_options[<?php echo esc_attr( $args['label_for'] ); ?>]">

        <option value="author" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'author', false ) ) : ( '' ); ?>>
            <?php esc_html_e( 'Author', 'HiveWP' ); ?>
        </option>

        <option value="community" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'community', false ) ) : ( '' ); ?>>
            <?php esc_html_e( 'Community', 'HiveWP' ); ?>
        </option>
    </select>

    
    <p class="description">
        <?php esc_html_e( 'Specified account is an author or community', 'HiveWP' ); ?>
    </p>


    <?php
}


/**
 * Publish/Draft field callback
 */
function HiveWP_field_publish_cb( $args ) {
    // Get the value of the setting we've registered with register_setting()
    $options = get_option( 'HiveWP_options' );
    ?>

    <select
            id="<?php echo esc_attr( $args['label_for'] ); ?>"
            data-custom="<?php echo esc_attr( $args['HiveWP_custom_data'] ); ?>"
            name="HiveWP_options[<?php echo esc_attr( $args['label_for'] ); ?>]">

        <option value="draft" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'draft', false ) ) : ( '' ); ?>>
            <?php esc_html_e( 'Draft', 'HiveWP' ); ?>
        </option>

        <option value="publish" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'publish', false ) ) : ( '' ); ?>>
            <?php esc_html_e( 'Published', 'HiveWP' ); ?>
        </option>
    </select>

    
    <p class="description">
        <?php esc_html_e( 'Import as Published or Draft posts', 'HiveWP' ); ?>
    </p>


    <?php
}


/**
 * Import schedule field callback
 */
function HiveWP_field_schedule_cb( $args ) {
    // Get the value of the setting we've registered with register_setting()
    $options = get_option( 'HiveWP_options' );
    ?>

    <select
            id="<?php echo esc_attr( $args['label_for'] ); ?>"
            data-custom="<?php echo esc_attr( $args['HiveWP_custom_data'] ); ?>"
            name="HiveWP_options[<?php echo esc_attr( $args['label_for'] ); ?>]">

        <option value="daily" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'daily', false ) ) : ( '' ); ?>>
            <?php esc_html_e( 'Daily', 'HiveWP' ); ?>
        </option>

        <option value="hourly" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'hourly', false ) ) : ( '' ); ?>>
            <?php esc_html_e( 'Hourly', 'HiveWP' ); ?>
        </option>

        <option value="30min" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], '30min', false ) ) : ( '' ); ?>>
            <?php esc_html_e( '30 Minutes', 'HiveWP' ); ?>
        </option>

        <option value="1min" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], '1min', false ) ) : ( '' ); ?>>
            <?php esc_html_e( '1 Minute', 'HiveWP' ); ?>
        </option>

        

        
    </select>

    
    <p class="description">
        <?php esc_html_e( 'How often to check for new Hive articles. Shorter delay will add more load to your site but might aid in initial testing.', 'HiveWP' ); ?>
    </p>


    <?php
}

/**
 * Qty field callback
 */
function HiveWP_field_qty_cb( $args ) {
    // Get the value of the setting we've registered with register_setting()
    $options = get_option( 'HiveWP_options' );
    ?>
          
             
    <input id="<?php echo esc_attr( $args['label_for'] ); ?>" type="text" 
    value="<?php echo isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : ( '30' ); ?>"
        
    data-custom="<?php echo esc_attr( $args['HiveWP_custom_data'] ); ?>" name="HiveWP_options[<?php echo esc_attr( $args['label_for'] ); ?>]">

          
    
    <p class="description">
        <?php esc_html_e( 'How many articles (maximum) to read each scheduled check. Note cross-posts will be skipped and there is a maximum of 100.', 'HiveWP' ); ?>
    </p>


    <?php
}



 
/**
 * Top level menu callback function
 */
function HiveWP_options_page_html() {
    // check user capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
 
    // add error/update messages
    // check if the user have submitted the settings
    // WordPress will add the "settings-updated" $_GET parameter to the url
    if ( isset( $_GET['settings-updated'] ) ) {
        // add settings saved message with the class of "updated"
        add_settings_error( 'HiveWP_messages', 'HiveWP_message', __( 'Settings Saved', 'HiveWP' ), 'updated' );

        $options = get_option( 'HiveWP_options' );
        $schedule = isset($options['HiveWP_field_schedule']) ? $options['HiveWP_field_schedule'] : ( 'daily' );

        // Clean up then add
        wp_clear_scheduled_hook( 'hivewp_cron' );
        wp_schedule_event( time(), $schedule, 'hivewp_cron' );
       

    }
 
    // show error/update messages
    settings_errors( 'HiveWP_messages' );
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="post">
            <?php
            // output fields for the registered setting "HiveWP"
            settings_fields( 'HiveWP' );
            // output setting sections and their fields
            // (sections are registered for "HiveWP", each field is registered to a specific section)
            do_settings_sections( 'HiveWP' );
            // output save settings button
            submit_button( 'Save Settings' );
            ?>
        </form>
    </div>
    <?php
}



/* ================================= */

// Include the markdown parser
include 'Parsedown.php';

    // Download image and store to WP
    function hivewp_upload_image( $image_to_get ) 
    {

        // for the download_url() and wp_handle_sideload() functions
        require_once( ABSPATH . 'wp-admin/includes/file.php' );

        // download to temp dir
        $this_image = download_url( $image_to_get );
        if( is_wp_error( $this_image ) ) {
            return false;
        }

        // move into uploads 
        $file = array(
            'name'     => basename( $image_to_get ),
            'type'     => mime_content_type( $this_image ),
            'tmp_name' => $this_image,
            'size'     => filesize( $this_image ),
        );

        // Use the WP sideload function
        $sideload = wp_handle_sideload(
            $file,
            array(
                'test_form'   => false // Don't check form 'action' 
            )
        );

        if( ! empty( $sideload[ 'error' ] ) ) {
            // you may return error message if you want
            error_log("Sideload Error:".$sideload[ 'error' ]);
        }

        // Add to WordPress media library
        $attachment_id = wp_insert_attachment(
            array(
                'guid'           => $sideload[ 'url' ],
                'post_mime_type' => $sideload[ 'type' ],
                'post_title'     => basename( $sideload[ 'file' ] ),
                'post_content'   => '',
                'post_status'    => 'inherit',
            ),
            $sideload[ 'file' ]
        );

        if( is_wp_error( $attachment_id ) || ! $attachment_id ) {
            error_log("Attachment Error");
        }

        // update medatata, regenerate image sizes
        require_once( ABSPATH . 'wp-admin/includes/image.php' );

        wp_update_attachment_metadata(
            $attachment_id,
            wp_generate_attachment_metadata( $attachment_id, $sideload[ 'file' ] )
        );

        //error_log("Added Image: ".$sideload['url']);
        return $sideload[ 'url' ];

    }

    function hivewp_cron() {

        // If the Hive user is set, use it
        $options = get_option( 'HiveWP_options' );
        $hive_user = isset($options['HiveWP_field_user']) ? $options['HiveWP_field_user'] : ( '' );
        $publish = isset($options['HiveWP_field_publish']) ? $options['HiveWP_field_publish'] : ( 'draft' );
        $qty = isset($options['HiveWP_field_qty']) ? $options['HiveWP_field_qty'] : ( '30' );

        $inc_tag = isset($options['HiveWP_field_inc_tag']) ? $options['HiveWP_field_inc_tag'] : ( '' );
        $ex_tag = isset($options['HiveWP_field_ex_tag']) ? $options['HiveWP_field_ex_tag'] : ( '' );

        $list_by_author = isset($options['HiveWP_field_by']) ? $options['HiveWP_field_by'] : ( 'author' );
        if($qty < 1 || $qty > 100) $qty = '30';

        if($hive_user=="") return;

        // Set up our query
        if ($list_by_author=="author")
        {
            $query = 'https://omiq.ca/Hive/feed.py?blog='.$hive_user.'&by=author&qty='.$qty;
        }
        else
        {
            $query = 'https://omiq.ca/Hive/feed.py?blog='.$hive_user.'&by=community&qty='.$qty;
        }

        if($inc_tag != "") 
        {
            $query .= '&tag=%'.$inc_tag.'%';
        }

        if($ex_tag != "") 
        {
            $query .= '&filter=%'.$ex_tag.'%';
        }


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $query);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        // Get the response from the API
        $response = curl_exec($ch);


         // Close the connection
        curl_close($ch);

        // Get the blog posts
        $posts=json_decode($response, TRUE);
        
        // Set up the parser
        $Parsedown = new Parsedown();

    if (!is_countable($posts) || $posts == "") {
        
        error_log("CURL:".$query);
        error_log("RESPONSE:".$response);
        error_log('Error Details: ' . curl_error($ch));

        exit("No results");
    }

        // Iterate through the returned posts
        for($x = 0; $x < count($posts); $x++) {

            // Pull out specifics
            $post=$posts[$x];
            $meta=json_decode($post['json_metadata'],TRUE);
            $image=$meta['image'];
            $tags=join(', ',$meta['tags']);
            $post_id = $post['ID'];

            
            if ( FALSE === get_post_status( $post_id ) ) {
                wp_insert_post($wp_post);

                if (! empty($image))
                {
                    foreach($image as $next_image)
                    {
                        $new_image_url = hivewp_upload_image($next_image);
                        $post['body']=str_ireplace($next_image, $new_image_url, $post['body']);
                    }
                }

                // Insert the post
                $wp_post = array(
                    'post_date'=>$post['created'],
                    'post_title'=>$post['title'],
                    'post_content'=>$Parsedown->text($post['body']),
                    'tags_input'=>$tags,
                    'import_id'=>$post_id,
                    'post_status'   => $publish,
                    );


            } else {
                error_log("Already exists");
            }
      
        }

        return;
    }
    
    add_action('hivewp_cron', 'hivewp_cron');
    
    add_filter( 'cron_schedules', 'hivewp_add_cron_interval' );
    function hivewp_add_cron_interval( $schedules ) { 
        $schedules['30min'] = array(
            'interval' => 60*30,
            'display'  => esc_html__( 'Every 30 Mins' ), );
  
        $schedules['1min'] = array(
            'interval' => 
            60,
            'display'  => esc_html__( 'Every Minute' ), );
        return $schedules;
    }



?>
