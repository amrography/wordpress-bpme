<?php
/**
 * Plugin Name: Bulk Photos Meta Editor
 * Plugin URI: https://www.amrography.com/
 * Description: Edit photos metadata in bulk
 * Version: 1.0.0
 * Author: Amro Khaled
 * Author URI: http://amrography.com/
 * Text Domain: bulk-photos-meta-editor
 * ////----------------------------////
 * @author Amro Khaled
 * @email hi@amrography.com
 * @create date 24-02-2020 11:30:05
 * @modify date 24-02-2020 21:12:40
 * @since 1.0.0
 * @version 1.0.0
 * @path amrography/wp-content/plugins/bulk-photos-meta-editor/bulk-photos-meta-editor.php
 * @desc Bulk photo metadata editor plugin
 * TODO: [] 
*/

// !! Security check point !!
if ( ! defined('ABSPATH') || ! function_exists( 'add_action' ) || ! function_exists( 'add_filter' )) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}
// !! Ready to go !
 
// 1: Add the option to select menu
function bpme_register_my_bulk_actions($bulk_actions) {
    $bulk_actions['bulk_photos_meta_editor'] = __( 'Bulk photos meta editor', 'bulk-photos-meta-editor');
    return $bulk_actions;
}
add_filter( 'bulk_actions-upload', 'bpme_register_my_bulk_actions' );

// 2: After select and submitting the button
function bpme_my_bulk_action_handler( $redirect_to, $action_name, $post_ids ) { 
    if ( 'bulk_photos_meta_editor' === $action_name ) {
        $ids = implode("_", $post_ids);
        $redirect_to = add_query_arg( 'page', 'bulk_photos_meta_editor', $redirect_to ); 
        $redirect_to = add_query_arg( 'bulk_photos_meta_editor_processed', $ids, $redirect_to ); 
        return $redirect_to;
    } else {
        return $redirect_to;
    }
}
add_filter( 'handle_bulk_actions-upload', 'bpme_my_bulk_action_handler', 10, 3 );

// 3: After redirecting to custom BPME admin page
function bpme_my_bulk_action_admin_notice() { 
    printf( 'Processed %s post.', 10);
}

// -: To add the page under Media Library menu item
function bpme_custom_admin_menu_button()
{
    add_submenu_page( 'upload.php', 'Bulk photo meta editor', 'BPME', 'manage_options', 'bulk_photos_meta_editor', 'bpme_custom_admin_index_page', 110);
}
add_action('admin_menu', 'bpme_custom_admin_menu_button');
// -: Import the BPME admin page
function bpme_custom_admin_index_page() {
    require_once plugin_dir_path(__FILE__) . 'templates/admin.php';
}

/**
 * Options registeration
 */
add_action( 'admin_init', 'bpme_custom_options' );

function bpme_custom_options() {
    session_start();

    add_settings_section( 'bpme-options', 'Photos details', 'bpme_additional_custom_options', 'bulk_photos_meta_editor' );

    if (isset($_SESSION['admin_notice_message']) && isset($_SESSION['admin_notice_message_type'])) {
        printf('<div class="notice notice-%s is-dismissible"><p>%s</p></div>', $_SESSION['admin_notice_message_type'], $_SESSION['admin_notice_message']);
        unset($_SESSION['admin_notice_message']);
        unset($_SESSION['admin_notice_message_type']);
    }

    if (isset($_GET['page']) && $_GET['page'] == 'bulk_photos_meta_editor' && !isset($_GET['bulk_photos_meta_editor_processed'])){
        $_SESSION['admin_notice_message'] = "Please select photos first";
        $_SESSION['admin_notice_message_type'] = "error";
        wp_safe_redirect( admin_url('/upload.php') );
    } else {
        bpme_custom_options_register();
    }
}

function bpme_additional_custom_options() {
    if (isset($_GET['bulk_photos_meta_editor_processed'])) {
        $ids = explode('_', $_GET['bulk_photos_meta_editor_processed']);
        $title = get_option('bpme-options-title');
        $caption = get_option('bpme-options-caption');
        $description = get_option('bpme-options-description');
        
        foreach ($ids as $id) {
            if ('publish' == get_post_status($id)) {
                $my_image_meta = array(
                    'ID' => $id,
                    'post_title' => $title,
                    'post_excerpt' => $caption,
                    'post_content' => $description,
                );
                    
                update_post_meta( $id, '_wp_attachment_image_alt', $title );
                wp_update_post( $my_image_meta );
            }
        }

        update_option('bpme-options-title', false);
        update_option('bpme-options-caption', false);
        update_option('bpme-options-description', false);
    } else {}
}

function bpme_custom_options_register() {
    register_setting( 'bpme-settings-group', 'bpme-options-title');
    add_settings_field( 'bpme-options-title', 'Title', 'bpme_html_the_input_field', 'bulk_photos_meta_editor', 'bpme-options', array( 'id' => 'bpme-options-title', 'title' => "Title"));
    register_setting( 'bpme-settings-group', 'bpme-options-caption');
    add_settings_field( 'bpme-options-caption', 'Caption', 'bpme_html_the_input_field', 'bulk_photos_meta_editor', 'bpme-options', array( 'id' => 'bpme-options-caption', 'title' => 'Caption'));
    register_setting( 'bpme-settings-group', 'bpme-options-description');
    add_settings_field( 'bpme-options-description', 'Description', 'bpme_html_the_input_field', 'bulk_photos_meta_editor', 'bpme-options', array( 'id' => 'bpme-options-description', 'title' => 'Description'));
}

function bpme_html_the_input_field($args) {
    $id = $args['id'];
    echo '<input type="text" name="'.$id.'" value="'.esc_attr(get_option($id)).'" placeholder="Enter '.$args['title'].'" />';
}
