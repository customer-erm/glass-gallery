<?php
/**
 * Plugin Name: Glass Gallery Pro
 * Plugin URI: https://eliteresultsmarketing.com
 * Description: A professional filterable image gallery with customer upload portal for glass companies
 * Version: 2.0.0
 * Author: Elite Results Marketing
 * License: GPL v2 or later
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('GLASS_GALLERY_URL', plugin_dir_url(__FILE__));
define('GLASS_GALLERY_PATH', plugin_dir_path(__FILE__));
define('GLASS_GALLERY_VERSION', '2.0.0');

class GlassGalleryPro {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // Create custom post type for gallery images
        $this->create_post_types();
        
        // Create custom taxonomies
        $this->create_taxonomies();
        
        // Add admin menus
        add_action('admin_menu', array($this, 'add_admin_menus'));
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Add shortcodes
        add_shortcode('glass_gallery', array($this, 'gallery_shortcode'));
        add_shortcode('glass_upload_form', array($this, 'upload_form_shortcode'));
        add_shortcode('glass_featured_gallery', array($this, 'featured_gallery_shortcode'));
        
        // Handle AJAX requests
// Handle AJAX requests
// Handle AJAX requests
add_action('wp_ajax_upload_gallery_image', array($this, 'handle_image_upload'));
add_action('wp_ajax_nopriv_upload_gallery_image', array($this, 'handle_image_upload'));
add_action('wp_ajax_filter_gallery_images', array($this, 'filter_gallery_images'));
add_action('wp_ajax_nopriv_filter_gallery_images', array($this, 'filter_gallery_images'));
add_action('wp_ajax_track_image_view', array($this, 'handle_track_image_view'));
add_action('wp_ajax_nopriv_track_image_view', array($this, 'handle_track_image_view'));

// Category management AJAX handlers
add_action('wp_ajax_add_gallery_term', array($this, 'handle_add_gallery_term'));
add_action('wp_ajax_edit_gallery_term', array($this, 'handle_edit_gallery_term'));
add_action('wp_ajax_delete_gallery_term', array($this, 'handle_delete_gallery_term'));
add_action('wp_ajax_export_gallery_categories', array($this, 'handle_export_gallery_categories'));
add_action('wp_ajax_populate_default_categories', array($this, 'handle_populate_default_categories'));
add_action('wp_ajax_save_category_relationships', array($this, 'handle_save_category_relationships'));
add_action('wp_ajax_get_category_relationships', array($this, 'handle_get_category_relationships'));
add_action('wp_ajax_get_parent_categories', array($this, 'handle_get_parent_categories'));
add_action('wp_ajax_add_custom_taxonomy', array($this, 'handle_add_custom_taxonomy'));
add_action('wp_ajax_delete_custom_taxonomy', array($this, 'handle_delete_custom_taxonomy'));
add_action('wp_ajax_toggle_taxonomy_visibility', array($this, 'handle_toggle_taxonomy_visibility'));


add_action('wp_ajax_batch_upload_gallery_images', array($this, 'handle_batch_image_upload'));
add_action('wp_ajax_nopriv_batch_upload_gallery_images', array($this, 'handle_batch_image_upload'));

// Gallery management AJAX handlers
add_action('wp_ajax_update_gallery_image', array($this, 'handle_update_gallery_image'));
add_action('wp_ajax_nopriv_update_gallery_image', array($this, 'handle_update_gallery_image'));
add_action('wp_ajax_delete_gallery_image', array($this, 'handle_delete_gallery_image'));
add_action('wp_ajax_nopriv_delete_gallery_image', array($this, 'handle_delete_gallery_image'));
add_action('wp_ajax_update_gallery_order', array($this, 'handle_update_gallery_order'));
add_action('wp_ajax_nopriv_update_gallery_order', array($this, 'handle_update_gallery_order'));
        // Add custom fields to post type
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_boxes'));
        
        // Add featured image column to admin list
        add_filter('manage_glass_gallery_item_posts_columns', array($this, 'add_image_column'));
        add_action('manage_glass_gallery_item_posts_custom_column', array($this, 'display_image_column'), 10, 2);
        add_action('admin_head', array($this, 'admin_column_width'));
    }
    
    public function activate() {
        $this->create_post_types();
        $this->create_taxonomies();
        $this->create_database_tables();
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        flush_rewrite_rules();
    }

    // Update gallery image details
public function handle_update_gallery_image() {
    check_ajax_referer('glass_gallery_nonce', 'nonce');
    
    $post_id = intval($_POST['post_id']);
    $field = sanitize_text_field($_POST['field']);
    $value = $_POST['value'];
    
    if (!current_user_can('edit_post', $post_id)) {
        wp_send_json_error('Insufficient permissions');
        return;
    }
    
    switch ($field) {
        case 'title':
            $result = wp_update_post(array(
                'ID' => $post_id,
                'post_title' => sanitize_text_field($value)
            ));
            break;
            
        case 'description':
            $result = update_post_meta($post_id, '_glass_image_caption', sanitize_textarea_field($value));
            break;
            

            

            
        case 'categories':
            $categories = json_decode(stripslashes($value), true);
            if (is_array($categories)) {
                foreach ($categories as $taxonomy => $terms) {
                    $result = wp_set_post_terms($post_id, array_map('intval', $terms), $taxonomy);
                }
            }
            break;
            
        default:
            wp_send_json_error('Invalid field');
            return;
    }
    
    if ($result !== false) {
        wp_send_json_success('Updated successfully');
    } else {
        wp_send_json_error('Update failed');
    }
}

// Delete gallery image
public function handle_delete_gallery_image() {
    check_ajax_referer('glass_gallery_nonce', 'nonce');
    
    $post_id = intval($_POST['post_id']);
    
    if (!current_user_can('delete_post', $post_id)) {
        wp_send_json_error('Insufficient permissions');
        return;
    }
    
    // Get attachment ID
    $attachment_id = get_post_thumbnail_id($post_id);
    
    // Delete the post
    $result = wp_delete_post($post_id, true);
    
    if ($result) {
        // Also delete the attachment if it exists
        if ($attachment_id) {
            wp_delete_attachment($attachment_id, true);
        }
        
        wp_send_json_success('Image deleted successfully');
    } else {
        wp_send_json_error('Failed to delete image');
    }
}

// Update gallery order
public function handle_update_gallery_order() {
    check_ajax_referer('glass_gallery_nonce', 'nonce');
    
    $order_data = json_decode(stripslashes($_POST['order_data']), true);
    
    if (!is_array($order_data)) {
        wp_send_json_error('Invalid order data');
        return;
    }
    
    foreach ($order_data as $item) {
        $post_id = intval($item['postId']);
        $order = intval($item['order']);
        
        if ($post_id > 0) {
            update_post_meta($post_id, '_glass_image_order', $order);
        }
    }
    
    wp_send_json_success('Order updated successfully');
}
    
    public function create_post_types() {
        register_post_type('glass_gallery_item', array(
            'labels' => array(
                'name' => 'Gallery Images',
                'singular_name' => 'Gallery Image',
                'add_new' => 'Add New Image',
                'add_new_item' => 'Add New Gallery Image',
                'edit_item' => 'Edit Gallery Image',
                'new_item' => 'New Gallery Image',
                'view_item' => 'View Gallery Image',
                'search_items' => 'Search Gallery Images',
                'not_found' => 'No gallery images found',
                'not_found_in_trash' => 'No gallery images found in trash'
            ),
            'public' => true,
            'show_in_menu' => false, // We'll add custom menu
            'supports' => array('title', 'editor', 'thumbnail', 'author'),
            'has_archive' => false,
            'rewrite' => array('slug' => 'gallery-item'),
'capability_type' => 'post',
'hierarchical' => false,
'taxonomies' => array('category', 'post_tag')
        ));
    }
    
    public function create_taxonomies() {
    // Register standard WordPress categories for this post type
    register_taxonomy_for_object_type('category', 'glass_gallery_item');
    
    // Remove post tags (we don't need them)
    register_taxonomy_for_object_type('post_tag', 'glass_gallery_item');
}
    
    public function create_database_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'glass_gallery_views';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            ip_address varchar(45) DEFAULT NULL,
            user_agent text,
            view_date datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY post_id (post_id),
            KEY view_date (view_date)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    public function add_admin_menus() {
        add_menu_page(
            'Glass Gallery Pro',
            'Glass Gallery',
            'manage_options',
            'glass-gallery-pro',
            array($this, 'admin_dashboard'),
            'dashicons-format-gallery',
            30
        );
        
        add_submenu_page(
            'glass-gallery-pro',
            'All Images',
            'All Images',
            'manage_options',
            'edit.php?post_type=glass_gallery_item'
        );
        
        add_submenu_page(
            'glass-gallery-pro',
            'Add New Image',
            'Add New Image',
            'manage_options',
            'post-new.php?post_type=glass_gallery_item'
        );
        
        add_submenu_page(
            'glass-gallery-pro',
            'Categories',
            'Categories',
            'manage_options',
            'glass-gallery-categories',
            array($this, 'categories_page')
        );
        
        add_submenu_page(
            'glass-gallery-pro',
            'Settings',
            'Settings',
            'manage_options',
            'glass-gallery-settings',
            array($this, 'settings_page')
        );
    }
    
    public function add_meta_boxes() {
        add_meta_box(
            'glass_gallery_details',
            'Gallery Image Details',
            array($this, 'gallery_details_metabox'),
            'glass_gallery_item',
            'normal',
            'high'
        );
    }
    
    public function gallery_details_metabox($post) {
        wp_nonce_field('glass_gallery_meta_nonce', 'glass_gallery_nonce');
        
        $image_caption = get_post_meta($post->ID, '_glass_image_caption', true);
        $image_order = get_post_meta($post->ID, '_glass_image_order', true);
        $featured_image = get_post_meta($post->ID, '_glass_featured_image', true);
        
        echo '<table class="form-table">';
        echo '<tr><th><label for="glass_image_caption">Image Caption</label></th>';
        echo '<td><textarea id="glass_image_caption" name="glass_image_caption" rows="3" cols="50">' . esc_textarea($image_caption) . '</textarea>';
        echo '<p class="description">This caption will appear below the image in the modal.</p></td></tr>';
        
        echo '<tr><th><label for="glass_image_order">Display Order</label></th>';
        echo '<td><input type="number" id="glass_image_order" name="glass_image_order" value="' . esc_attr($image_order) . '" />';
        echo '<p class="description">Lower numbers appear first. Leave blank for chronological order.</p></td></tr>';
        
        echo '<tr><th><label for="glass_featured_image">Featured Image</label></th>';
        echo '<td><label><input type="checkbox" id="glass_featured_image" name="glass_featured_image" value="1" ' . checked($featured_image, 1, false) . ' /> Mark as featured image</label>';
        echo '<p class="description">Featured images appear first in their categories.</p></td></tr>';
        
        echo '</table>';
    }
    
    public function save_meta_boxes($post_id) {
        if (!isset($_POST['glass_gallery_nonce']) || !wp_verify_nonce($_POST['glass_gallery_nonce'], 'glass_gallery_meta_nonce')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (isset($_POST['glass_image_caption'])) {
            update_post_meta($post_id, '_glass_image_caption', sanitize_textarea_field($_POST['glass_image_caption']));
        }
        
        if (isset($_POST['glass_image_order'])) {
            update_post_meta($post_id, '_glass_image_order', intval($_POST['glass_image_order']));
        }
        
        update_post_meta($post_id, '_glass_featured_image', isset($_POST['glass_featured_image']) ? 1 : 0);
    }

       // Add featured image column to admin posts list
    public function add_image_column($columns) {
        $new_columns = array();
        
        // Add checkbox column first
        if (isset($columns['cb'])) {
            $new_columns['cb'] = $columns['cb'];
            unset($columns['cb']);
        }
        
        // Add featured image column
        $new_columns['featured_image'] = 'Image';
        
        // Add remaining columns
        $new_columns = array_merge($new_columns, $columns);
        
        return $new_columns;
    }
    
    // Display the featured image in the column
    public function display_image_column($column, $post_id) {
        if ($column === 'featured_image') {
            $thumbnail = get_the_post_thumbnail($post_id, array(60, 60), array(
                'style' => 'width: 60px; height: 60px; object-fit: cover; border-radius: 4px; display: block;'
            ));
            
            if ($thumbnail) {
                echo '<a href="' . get_edit_post_link($post_id) . '">' . $thumbnail . '</a>';
            } else {
                echo '<span style="display: inline-block; width: 60px; height: 60px; background: #f0f0f1; border-radius: 4px; text-align: center; line-height: 60px; color: #a7aaad;">—</span>';
            }
        }
    }
    
    // Set column width for image column
    public function admin_column_width() {
        echo '<style>
            .column-featured_image {
                width: 80px;
                text-align: center;
            }
            .column-featured_image img {
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
        </style>';
    }
    
public function enqueue_frontend_assets() {
    // Simple, clean enqueue - version 2.0.0
    $ver = GLASS_GALLERY_VERSION . '.' . time();

    // NO jQuery dependency - vanilla JS
    wp_enqueue_script('ggp-frontend', GLASS_GALLERY_URL . 'assets/js/frontend.js', array(), $ver, true);
    wp_enqueue_style('ggp-frontend-css', GLASS_GALLERY_URL . 'assets/css/frontend.css', array(), $ver);
    wp_enqueue_script('ggp-upload', GLASS_GALLERY_URL . 'assets/js/upload-manager.js', array('jquery'), $ver, true);

    // Featured Gallery (carousel & modal)
    wp_enqueue_script('ggp-featured-gallery', GLASS_GALLERY_URL . 'assets/js/featured-gallery.js', array(), $ver, true);
    wp_enqueue_style('ggp-featured-gallery-css', GLASS_GALLERY_URL . 'assets/css/featured-gallery.css', array(), $ver);

    // Ajax for upload only
    wp_localize_script('ggp-upload', 'glass_gallery_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('glass_gallery_nonce')
    ));
}
    
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'glass-gallery') !== false) {
            wp_enqueue_script('glass-gallery-admin-js', GLASS_GALLERY_URL . 'assets/js/admin.js', array('jquery'), GLASS_GALLERY_VERSION, true);
            wp_enqueue_style('glass-gallery-admin-css', GLASS_GALLERY_URL . 'assets/css/admin.css', array(), GLASS_GALLERY_VERSION);
        }
    }
    
    // Admin Dashboard
    public function admin_dashboard() {
        include GLASS_GALLERY_PATH . 'templates/admin-dashboard.php';
    }
    
public function categories_page() {
        include GLASS_GALLERY_PATH . 'templates/admin-categories.php';
    }
    
    // Get category relationships
    public function get_category_relationships() {
        return get_option('glass_gallery_category_relationships', array());
    }
    
    // Save category relationships
    public function save_category_relationships($relationships) {
        update_option('glass_gallery_category_relationships', $relationships);
    }
    
    // Settings Page
    public function settings_page() {
        include GLASS_GALLERY_PATH . 'templates/admin-settings.php';
    }
    
    // Shortcode for gallery display
    public function gallery_shortcode($atts) {
        $atts = shortcode_atts(array(
            'category' => '',
            'limit' => -1,
            'columns' => 4,
            'show_filters' => 'true',
            'featured_only' => 'false'
        ), $atts);
        
        ob_start();
        include GLASS_GALLERY_PATH . 'templates/gallery-display.php';
        return ob_get_clean();
    }
    
    // Shortcode for upload form
    public function upload_form_shortcode($atts) {
        $atts = shortcode_atts(array(
            'user_role' => 'subscriber',
            'redirect_after_upload' => ''
        ), $atts);

        ob_start();
        include GLASS_GALLERY_PATH . 'templates/upload-form.php';
        return ob_get_clean();
    }

    // Shortcode for featured gallery (random images with link to full gallery)
    public function featured_gallery_shortcode($atts) {
        $atts = shortcode_atts(array(
            'category' => '',
            'limit' => 12,
            'button_text' => 'View Full Gallery',
            'button_url' => '/gallery',
            'carousel' => 'false'
        ), $atts);

        ob_start();
        include GLASS_GALLERY_PATH . 'templates/featured-gallery.php';
        return ob_get_clean();
    }

    // Handle batch image upload
public function handle_batch_image_upload() {
    check_ajax_referer('glass_gallery_nonce', 'nonce');
    
    if (!current_user_can('upload_files')) {
        wp_send_json_error('Insufficient permissions');
        return;
    }
    
    $images_data = json_decode(stripslashes($_POST['images_data']), true);
    $results = array();
    
    foreach ($images_data as $index => $image_data) {
        if (!isset($_FILES['gallery_images']['tmp_name'][$index])) {
            continue;
        }
        
        $file_array = array(
            'name' => $_FILES['gallery_images']['name'][$index],
            'type' => $_FILES['gallery_images']['type'][$index],
            'tmp_name' => $_FILES['gallery_images']['tmp_name'][$index],
            'error' => $_FILES['gallery_images']['error'][$index],
            'size' => $_FILES['gallery_images']['size'][$index]
        );
        
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        
        $upload_overrides = array('test_form' => false);
        $movefile = wp_handle_upload($file_array, $upload_overrides);
        
        if ($movefile && !isset($movefile['error'])) {
            // Create attachment
            $wp_filetype = wp_check_filetype($movefile['file'], null);
            $attachment = array(
                'post_mime_type' => $wp_filetype['type'],
                'post_title' => sanitize_text_field($image_data['title']),
                'post_content' => '',
                'post_status' => 'inherit'
            );
            
            $attach_id = wp_insert_attachment($attachment, $movefile['file']);
            
            if (!is_wp_error($attach_id)) {
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                $attach_data = wp_generate_attachment_metadata($attach_id, $movefile['file']);
                wp_update_attachment_metadata($attach_id, $attach_data);
                
                // Create gallery post
                $post_data = array(
                    'post_title' => sanitize_text_field($image_data['title']),
                    'post_type' => 'glass_gallery_item',
                    'post_status' => 'publish',
                    'menu_order' => isset($image_data['order']) ? intval($image_data['order']) : 0,
                    'meta_input' => array(
                        '_glass_image_caption' => sanitize_textarea_field($image_data['description'])
                    )
                );
                
                $post_id = wp_insert_post($post_data);
                
                if (!is_wp_error($post_id)) {
                    // Set featured image
                    set_post_thumbnail($post_id, $attach_id);
                    
                    // Set categories
                    if (!empty($image_data['categories'])) {
                        foreach ($image_data['categories'] as $taxonomy => $terms) {
                            if (!empty($terms)) {
                                wp_set_post_terms($post_id, $terms, $taxonomy);
                            }
                        }
                    }
                    
                    $results[] = array(
                        'success' => true,
                        'post_id' => $post_id,
                        'title' => $image_data['title']
                    );
                } else {
                    $results[] = array(
                        'success' => false,
                        'error' => 'Failed to create gallery item',
                        'title' => $image_data['title']
                    );
                }
            } else {
                $results[] = array(
                    'success' => false,
                    'error' => 'Failed to create attachment',
                    'title' => $image_data['title']
                );
            }
        } else {
            $results[] = array(
                'success' => false,
                'error' => isset($movefile['error']) ? $movefile['error'] : 'Upload failed',
                'title' => $image_data['title']
            );
        }
    }
    
    wp_send_json_success($results);
}
    
    // Handle AJAX image upload
    public function handle_image_upload() {
        check_ajax_referer('glass_gallery_nonce', 'nonce');
        
        if (!current_user_can('upload_files')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }
        
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        
        $uploadedfile = $_FILES['gallery_image'];
        $upload_overrides = array('test_form' => false);
        
        $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
        
        if ($movefile && !isset($movefile['error'])) {
            // Create attachment
            $wp_filetype = wp_check_filetype($movefile['file'], null);
            $attachment = array(
                'post_mime_type' => $wp_filetype['type'],
                'post_title' => sanitize_text_field($_POST['image_title']),
                'post_content' => '',
                'post_status' => 'inherit'
            );
            
            $attach_id = wp_insert_attachment($attachment, $movefile['file']);
            
            if (!is_wp_error($attach_id)) {
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                $attach_data = wp_generate_attachment_metadata($attach_id, $movefile['file']);
                wp_update_attachment_metadata($attach_id, $attach_data);
                
                // Create gallery post
               $post_data = array(
    'post_title' => sanitize_text_field($_POST['image_title']),
    'post_type' => 'glass_gallery_item',
    'post_status' => 'publish',
    'meta_input' => array(
        '_glass_image_caption' => sanitize_textarea_field($_POST['image_caption'])
    )
);

if (isset($_POST['image_order'])) {
    $post_data['meta_input']['_glass_image_order'] = intval($_POST['image_order']);
}
                
                $post_id = wp_insert_post($post_data);
                
                if (!is_wp_error($post_id)) {
                    // Set featured image
                    set_post_thumbnail($post_id, $attach_id);
                    
                    // Set categories
if (!empty($_POST['categories'])) {
    $categories = json_decode(stripslashes($_POST['categories']), true);
    if (is_array($categories)) {
        // Support both formats: simple array of IDs or taxonomy => terms array
        if (isset($categories['category']) || isset($categories[0])) {
            // New format: taxonomy => terms
            if (isset($categories['category'])) {
                wp_set_post_terms($post_id, array_map('intval', $categories['category']), 'category');
            } else {
                // Old format: simple array of IDs (legacy support)
                wp_set_post_terms($post_id, array_map('intval', $categories), 'category');
            }
        } else {
            // Multiple taxonomies
            foreach ($categories as $taxonomy => $terms) {
                if (!empty($terms)) {
                    wp_set_post_terms($post_id, array_map('intval', $terms), $taxonomy);
                }
            }
        }
    }
}
                    
                    wp_send_json_success(array(
                        'message' => 'Image uploaded successfully!',
                        'post_id' => $post_id
                    ));
                } else {
                    wp_send_json_error('Error creating gallery item');
                }
            } else {
                wp_send_json_error('Error creating attachment');
            }
        } else {
            wp_send_json_error($movefile['error']);
        }
    }
    
    // Handle AJAX filtering
    public function filter_gallery_images() {
        check_ajax_referer('glass_gallery_nonce', 'nonce');
        
        $filters = isset($_POST['filters']) ? json_decode(stripslashes($_POST['filters']), true) : array();
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 20;
        
        // Use random order when no filters, otherwise use date order
        $has_filters = !empty($filters['categories']);

        $args = array(
            'post_type' => 'glass_gallery_item',
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'orderby' => $has_filters ? 'date' : 'rand',
            'order' => 'DESC'
        );
        
        // Add search query
        if (!empty($search)) {
            $args['s'] = $search;
        }
        
        // Add category filters
      // Add category filters with OR logic
        if (!empty($filters) && !empty($filters['categories'])) {
            $selected_terms = $filters['categories'];
            
            // Remove parent categories if their children are also selected
            $filtered_terms = array();
            foreach ($selected_terms as $term_id) {
                $has_selected_child = false;
                
                // Check if any other selected term is a child of this one
                foreach ($selected_terms as $other_term_id) {
                    if ($term_id !== $other_term_id) {
                        $other_term = get_term($other_term_id, 'category');
                        if ($other_term && !is_wp_error($other_term)) {
                            // Check if this term is a parent of the other term
                            if ($other_term->parent == $term_id) {
                                $has_selected_child = true;
                                break;
                            }
                        }
                    }
                }
                
                // Only include this term if it doesn't have a selected child
                if (!$has_selected_child) {
                    $filtered_terms[] = $term_id;
                }
            }
            
            // Use simple IN query - show images matching ANY selected category
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'category',
                    'field' => 'term_id',
                    'terms' => $filtered_terms,
                    'operator' => 'IN'
                )
            );
        }
        
        $query = new WP_Query($args);
        
        $images = array();
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
$images[] = array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'image_url' => get_the_post_thumbnail_url(get_the_ID(), 'large'),
                    'full_image_url' => get_the_post_thumbnail_url(get_the_ID(), 'full'),
                    'caption' => get_post_meta(get_the_ID(), '_glass_image_caption', true),
                    'categories' => $this->get_post_categories(get_the_ID())
                );
            }
        }
        
        wp_reset_postdata();
        
        wp_send_json_success(array(
            'images' => $images,
            'total_pages' => $query->max_num_pages,
            'found_posts' => $query->found_posts
        ));
    }
    
private function get_post_categories($post_id) {
    $terms = get_the_terms($post_id, 'category');
    if ($terms && !is_wp_error($terms)) {
        // Return array of category IDs only (for consistency with front-end display)
        return array_map(function($term) {
            return $term->term_id;
        }, $terms);
    }
    return array();
}
    // Handle AJAX add term
public function handle_add_gallery_term() {
    check_ajax_referer('gallery_categories_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    $taxonomy = sanitize_text_field($_POST['taxonomy']);
    $term_name = sanitize_text_field($_POST['term_name']);
    $term_slug = sanitize_text_field($_POST['term_slug']);
    $term_description = sanitize_textarea_field($_POST['term_description']);
    
    if (empty($term_name)) {
        wp_send_json_error('Term name is required');
    }
    
    $term_data = array(
        'name' => $term_name,
        'slug' => $term_slug,
        'description' => $term_description
    );
    
    $result = wp_insert_term($term_name, $taxonomy, $term_data);
    
    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    } else {
        wp_send_json_success(array(
            'term_id' => $result['term_id'],
            'message' => 'Term added successfully'
        ));
    }
}

// Handle AJAX edit term
public function handle_edit_gallery_term() {
    check_ajax_referer('gallery_categories_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    $term_id = intval($_POST['term_id']);
    $taxonomy = sanitize_text_field($_POST['taxonomy']);
    $term_name = sanitize_text_field($_POST['term_name']);
    $term_slug = sanitize_text_field($_POST['term_slug']);
    $term_description = sanitize_textarea_field($_POST['term_description']);
    
    if (empty($term_name)) {
        wp_send_json_error('Term name is required');
    }
    
    $term_data = array(
        'name' => $term_name,
        'slug' => $term_slug,
        'description' => $term_description
    );
    
    $result = wp_update_term($term_id, $taxonomy, $term_data);
    
    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    } else {
        wp_send_json_success(array(
            'message' => 'Term updated successfully'
        ));
    }
}

// Handle AJAX delete term
public function handle_delete_gallery_term() {
    check_ajax_referer('gallery_categories_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    $term_id = intval($_POST['term_id']);
    $taxonomy = sanitize_text_field($_POST['taxonomy']);
    
    $result = wp_delete_term($term_id, $taxonomy);
    
    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    } else {
        wp_send_json_success(array(
            'message' => 'Term deleted successfully'
        ));
    }
}

// Handle AJAX export categories
public function handle_export_gallery_categories() {
    check_ajax_referer('gallery_categories_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    $taxonomies = array(
        'glass_product_type' => 'Product Types',
        'shower_shape' => 'Shower Shapes',
        'glass_function' => 'Functions',
        'color_option' => 'Color Options',
        'handle_style' => 'Handle Styles',
        'glass_feature' => 'Features',
        'framing_type' => 'Framing Types',
        'glass_type' => 'Glass Types',
        'hinge_type' => 'Hinge Types'
    );
    
    $export_data = array(
        'version' => '1.0',
        'export_date' => current_time('c'),
        'plugin' => 'Glass Gallery Pro',
        'description' => 'Exported categories from Glass Gallery Pro',
        'categories' => array()
    );
    
    foreach ($taxonomies as $taxonomy => $label) {
        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC'
        ));
        
        if (!empty($terms) && !is_wp_error($terms)) {
            $export_data['categories'][$taxonomy] = array(
                'taxonomy' => $taxonomy,
                'label' => $label,
                'terms' => array()
            );
            
            foreach ($terms as $term) {
                $export_data['categories'][$taxonomy]['terms'][] = array(
                    'name' => $term->name,
                    'slug' => $term->slug,
                    'description' => $term->description
                );
            }
        }
    }
    
    wp_send_json_success($export_data);
}

// Handle AJAX populate default categories
public function handle_populate_default_categories() {
    check_ajax_referer('populate_categories', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    $default_terms = array(
        'glass_product_type' => array(
            'Shower Enclosures',
            'Wine Cellars',
            'Room Partitions',
            'Stair Rails & Wind Screens',
            'Mirrors',
            'Sauna Glass',
            'Furnishings',
            'Sneeze Guards'
        ),
        'shower_shape' => array(
            'Single Door',
            'Inline',
            'Corner',
            'Neo-Angle',
            'Shower Screen',
            'Glass on Tub'
        ),
        'glass_function' => array(
            'Swinging',
            'Single Sliding',
            'Double Sliding',
            'Bi-Fold'
        ),
        'glass_type' => array(
            'Regular Clear (Standard)',
            'Low Iron (Ultra Clear)',
            'Satin/Etched',
            'Rain or Niagara',
            'Color',
            'Mirror',
            'Reeded Glass'
        ),
        'color_option' => array(
            'Clear',
            'Bronze',
            'Grey',
            'Black',
            'White',
            'Frosted'
        ),
        'handle_style' => array(
            'Bar Handle',
            'Towel Bar',
            'Knob Handle',
            'Ladder Pull',
            'No Handle'
        ),
        'framing_type' => array(
            'Frameless',
            'Semi-Frameless',
            'Framed',
            'Heavy Glass'
        ),
        'glass_feature' => array(
            'Easy Clean Coating',
            'Tempered Safety Glass',
            'Custom Etching',
            'Privacy Glass'
        ),
        'hinge_type' => array(
            'Continuous Hinge',
            'Pivot Hinge',
            'Wall Mount Hinge',
            'Glass-to-Glass Hinge'
        )
    );
    
    $added_count = 0;
    $errors = array();
    
    foreach ($default_terms as $taxonomy => $terms) {
        foreach ($terms as $term_name) {
            if (!term_exists($term_name, $taxonomy)) {
                $result = wp_insert_term($term_name, $taxonomy);
                if (!is_wp_error($result)) {
                    $added_count++;
                } else {
                    $errors[] = "Failed to add '$term_name' to $taxonomy: " . $result->get_error_message();
                }
            }
        }
    }
    
    wp_send_json_success(array(
        'added_count' => $added_count,
        'errors' => $errors,
        'message' => "Added $added_count default categories successfully"
    ));
}

public function handle_track_image_view() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'glass_gallery_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }
    
    $post_id = intval($_POST['post_id']);
    
    if (!$post_id || get_post_type($post_id) !== 'glass_gallery_item') {
        wp_send_json_error('Invalid post ID');
        return;
    }
    
    // Track the view in the database
    global $wpdb;
    $table_name = $wpdb->prefix . 'glass_gallery_views';
    
    $result = $wpdb->insert(
        $table_name,
        array(
            'post_id' => $post_id,
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'view_date' => current_time('mysql')
        ),
        array('%d', '%s', '%s', '%s')
    );
    
    wp_send_json_success('View tracked');
}

public function handle_get_parent_categories() {
    check_ajax_referer('gallery_categories_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    $parent_terms = get_terms(array(
        'taxonomy' => 'gallery_parent_category',
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'ASC'
    ));
    
    if (is_wp_error($parent_terms)) {
        wp_send_json_error($parent_terms->get_error_message());
    }
    
    wp_send_json_success($parent_terms);
}


public function handle_get_category_relationships() {
    check_ajax_referer('gallery_categories_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    $relationships = $this->get_category_relationships();
    
    wp_send_json_success($relationships);
}

// Handle saving category relationships
public function handle_save_category_relationships() {
    check_ajax_referer('gallery_categories_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    $relationships = json_decode(stripslashes($_POST['relationships']), true);
    
    if (!is_array($relationships)) {
        wp_send_json_error('Invalid data format');
    }
    
    $this->save_category_relationships($relationships);
    
    wp_send_json_success(array('message' => 'Relationships saved successfully'));
}

public function handle_add_custom_taxonomy() {
    check_ajax_referer('gallery_categories_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    $slug = sanitize_key($_POST['taxonomy_slug']);
    $label = sanitize_text_field($_POST['taxonomy_label']);
    
    if (empty($slug) || empty($label)) {
        wp_send_json_error('Slug and label are required');
    }
    
    $custom_taxonomies = get_option('glass_gallery_custom_taxonomies', array());
    $custom_taxonomies[$slug] = $label;
    update_option('glass_gallery_custom_taxonomies', $custom_taxonomies);
    
    // Register the new taxonomy
    register_taxonomy($slug, 'glass_gallery_item', array(
        'labels' => array(
            'name' => $label,
            'singular_name' => rtrim($label, 's')
        ),
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => $slug)
    ));
    
    flush_rewrite_rules();
    
    wp_send_json_success(array('message' => 'Taxonomy added successfully'));
}

public function handle_delete_custom_taxonomy() {
    check_ajax_referer('gallery_categories_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    $slug = sanitize_key($_POST['taxonomy_slug']);
    
    if (empty($slug) || $slug === 'gallery_parent_category') {
        wp_send_json_error('Invalid taxonomy');
    }
    
    global $wp_taxonomies;
    
    // Delete all terms in this taxonomy
    $terms = get_terms(array('taxonomy' => $slug, 'hide_empty' => false));
    if (!is_wp_error($terms)) {
        foreach ($terms as $term) {
            wp_delete_term($term->term_id, $slug);
        }
    }
    
    // Remove from custom taxonomies option
    $custom_taxonomies = get_option('glass_gallery_custom_taxonomies', array());
    unset($custom_taxonomies[$slug]);
    update_option('glass_gallery_custom_taxonomies', $custom_taxonomies);
    
    // Unregister the taxonomy
    if (isset($wp_taxonomies[$slug])) {
        unset($wp_taxonomies[$slug]);
    }
    
    // Remove from category relationships
    $relationships = $this->get_category_relationships();
    foreach ($relationships as $parent_id => $taxonomies) {
        if (($key = array_search($slug, $taxonomies)) !== false) {
            unset($relationships[$parent_id][$key]);
        }
    }
    $this->save_category_relationships($relationships);
    
    flush_rewrite_rules();
    
    wp_send_json_success(array('message' => 'Taxonomy section deleted successfully'));
}
public function handle_toggle_taxonomy_visibility() {
    check_ajax_referer('gallery_categories_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    $taxonomy = sanitize_key($_POST['taxonomy']);
    $visible = $_POST['visible'] === '1';
    
    $visibility_settings = get_option('glass_gallery_taxonomy_visibility', array());
    $visibility_settings[$taxonomy] = $visible;
    update_option('glass_gallery_taxonomy_visibility', $visibility_settings);
    
    wp_send_json_success(array('message' => 'Visibility updated'));
}
}

// Add to glass-gallery-pro.php after the class definition

function glass_gallery_migrate_to_standard_categories() {
    global $wpdb;
    
    // Get all gallery items
    $posts = get_posts(array(
        'post_type' => 'glass_gallery_item',
        'posts_per_page' => -1,
        'post_status' => 'any'
    ));
    
    foreach ($posts as $post) {
        $all_terms = array();
        
        // Get terms from all old taxonomies
        $old_taxonomies = array(
            'glass_product_type', 'shower_shape', 'glass_function',
            'color_option', 'handle_style', 'glass_feature',
            'framing_type', 'glass_type', 'hinge_type', 'gallery_parent_category'
        );
        
        foreach ($old_taxonomies as $old_tax) {
            $terms = wp_get_post_terms($post->ID, $old_tax, array('fields' => 'ids'));
            if (!is_wp_error($terms)) {
                $all_terms = array_merge($all_terms, $terms);
            }
        }
        
        // Assign all terms to standard categories
        if (!empty($all_terms)) {
            wp_set_post_terms($post->ID, $all_terms, 'category', true);
        }
    }
    
    update_option('glass_gallery_migrated_categories', true);
}

// Run migration once
if (!get_option('glass_gallery_migrated_categories')) {
    glass_gallery_migrate_to_standard_categories();
}

// Initialize the plugin
new GlassGalleryPro();