<?php
/**
 * Plugin Name: Testimonials Pro
 * Plugin URI: https://github.com/codewithhamza1/testimonials-pro/
 * Description: Simple testimonial submission and display system with shortcodes.
 * Version: 1.0.1
 * Author: Muhammad Hamza
 * Author URI: https://github.com/codewithhamza1
 * License: GPL v2 or later
 * Text Domain: testimonials-pro
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 * Update URI: false
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('TESTIMONIALS_PRO_VERSION', '1.0.1');
define('TESTIMONIALS_PRO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TESTIMONIALS_PRO_PLUGIN_URL', plugin_dir_url(__FILE__));

// Prevent false update notifications
add_filter('site_transient_update_plugins', 'testimonials_pro_disable_updates');
function testimonials_pro_disable_updates($value) {
    if (isset($value->response[plugin_basename(__FILE__)])) {
        unset($value->response[plugin_basename(__FILE__)]);
    }
    return $value;
}

// Main plugin class
class TestimonialsPro {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // Load text domain
        load_plugin_textdomain('testimonials-pro', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Register post type
        $this->register_testimonial_post_type();
        
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Add hooks for testimonial status
        add_action('transition_post_status', array($this, 'handle_testimonial_status_change'), 10, 3);
        
        // Initialize components
        $this->init_components();
    }
    
    public function register_testimonial_post_type() {
        $labels = array(
            'name' => 'Testimonials',
            'singular_name' => 'Testimonial',
            'menu_name' => 'Testimonials',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Testimonial',
            'edit_item' => 'Edit Testimonial',
            'view_item' => 'View Testimonial',
            'all_items' => 'All Testimonials',
            'search_items' => 'Search Testimonials',
            'not_found' => 'No testimonials found.',
            'not_found_in_trash' => 'No testimonials found in Trash.'
        );

        $args = array(
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-format-quote',
            'supports' => array('title', 'editor'),
            'capability_type' => 'post'
        );

        register_post_type('testimonial', $args);
    }
    
    public function add_admin_menu() {
        // Add settings submenu
        add_submenu_page(
            'edit.php?post_type=testimonial',
            'Settings',
            'Settings',
            'manage_options',
            'testimonials-settings',
            array($this, 'settings_page')
        );
    }
    
    public function settings_page() {
        // Initialize settings class for full functionality
        if (class_exists('TestimonialsPro_Settings')) {
            $settings = new TestimonialsPro_Settings();
            $settings->settings_page();
        } else {
            echo '<div class="wrap"><h1>Settings</h1><p>Settings functionality is being loaded...</p></div>';
        }
    }
    
    private function init_components() {
        // Include and initialize components
        require_once TESTIMONIALS_PRO_PLUGIN_DIR . 'includes/class-shortcodes.php';
        require_once TESTIMONIALS_PRO_PLUGIN_DIR . 'includes/class-ajax.php';
        require_once TESTIMONIALS_PRO_PLUGIN_DIR . 'includes/class-frontend.php';
        require_once TESTIMONIALS_PRO_PLUGIN_DIR . 'includes/class-settings.php';
        require_once TESTIMONIALS_PRO_PLUGIN_DIR . 'includes/class-admin.php';
        
        new TestimonialsPro_Shortcodes();
        new TestimonialsPro_Ajax();
        new TestimonialsPro_Frontend();
        new TestimonialsPro_Settings();
        new TestimonialsPro_Admin();
    }
    
    public function activate() {
        // Create database table
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'testimonials_pro';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            title varchar(200) DEFAULT '',
            rating tinyint(1) NOT NULL DEFAULT 5,
            review text NOT NULL,
            status varchar(20) DEFAULT 'pending',
            submitted_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        
        // Add default testimonials
        $this->add_default_testimonials();
        
        update_option('testimonials_pro_db_version', TESTIMONIALS_PRO_VERSION);
    }
    
    private function add_default_testimonials() {
        // Check if testimonials already exist
        $existing_posts = get_posts(array(
            'post_type' => 'testimonial',
            'post_status' => 'publish',
            'posts_per_page' => 1
        ));
        
        if (!empty($existing_posts)) {
            return; // Don't add defaults if testimonials already exist
        }
        
        $default_testimonials = array(
            array(
                'name' => 'Sarah L.',
                'email' => 'sarah.lane@gmail.com',
                'title' => 'ProCell Microchanneling Client',
                'rating' => 5,
                'review' => 'I tried ProCell Microchanneling for the first time and was amazed at the results. My skin feels tighter, smoother, and so much more radiant. I get compliments all the time now!',
                'status' => 'approved'
            ),
            array(
                'name' => 'Megan P.',
                'email' => 'meganpaul89@hotmail.com',
                'title' => 'HydroDiamond Facial Client',
                'rating' => 5,
                'review' => 'The HydroDiamond Facial completely changed how my skin feels. It\'s cleaner, softer, and has a glow that lasts for days. I\'ve already booked my next one!',
                'status' => 'approved'
            ),
            array(
                'name' => 'Olivia T.',
                'email' => 'oliviaturner91@yahoo.com',
                'title' => 'Acne Management Client',
                'rating' => 5,
                'review' => 'I\'ve battled acne for most of my adult life. After just two Acne Management Facials, my skin finally started to clear. I\'m so grateful for this place!',
                'status' => 'approved'
            ),
            array(
                'name' => 'Linda C.',
                'email' => 'linda.carter22@gmail.com',
                'title' => 'Age Management Client',
                'rating' => 5,
                'review' => 'I had the Age Management Facial, and my skin looked instantly firmer and brighter. It\'s refreshing to find a place that combines relaxation with visible improvement.',
                'status' => 'approved'
            ),
            array(
                'name' => 'Jason B.',
                'email' => 'jbrown.men@gmail.com',
                'title' => 'Gentleman\'s Facial Client',
                'rating' => 5,
                'review' => 'I didn\'t realize how much my skin needed care until I tried the Gentleman\'s Facial. It helped calm irritation and made my skin feel smoother than ever.',
                'status' => 'approved'
            ),
            array(
                'name' => 'Jennifer S.',
                'email' => 'jenniferscott23@outlook.com',
                'title' => 'Calming Facial Client',
                'rating' => 5,
                'review' => 'I\'ve struggled with rosacea for years, but the Calming Facial made such a difference. My redness has reduced so much, and my skin feels balanced and hydrated.',
                'status' => 'approved'
            )
        );
        
        // Create WordPress posts for default testimonials
        foreach ($default_testimonials as $testimonial) {
            $post_data = array(
                'post_title' => $testimonial['name'],
                'post_content' => $testimonial['review'],
                'post_status' => 'publish',
                'post_type' => 'testimonial',
                'post_author' => 1
            );
            
            $post_id = wp_insert_post($post_data);
            
            if ($post_id) {
                // Add custom fields
                update_post_meta($post_id, 'testimonial_email', $testimonial['email']);
                update_post_meta($post_id, 'testimonial_title', $testimonial['title']);
                update_post_meta($post_id, 'testimonial_rating', $testimonial['rating']);
                update_post_meta($post_id, 'testimonial_status', $testimonial['status']);
                update_post_meta($post_id, 'testimonial_submitted_at', current_time('mysql'));
            }
        }
    }
    
    public function handle_testimonial_status_change($new_status, $old_status, $post) {
        // Only handle testimonial post type
        if ($post->post_type !== 'testimonial') {
            return;
        }
        
        // If testimonial is being published, set status to approved
        if ($new_status === 'publish' && $old_status !== 'publish') {
            update_post_meta($post->ID, 'testimonial_status', 'approved');
            
            // Also update the custom table if it exists
            global $wpdb;
            $table_name = $wpdb->prefix . 'testimonials_pro';
            
            // Check if table exists
            if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
                $wpdb->update(
                    $table_name,
                    array('status' => 'approved'),
                    array('name' => $post->post_title),
                    array('%s'),
                    array('%s')
                );
            }
        }
        
        // If testimonial is being unpublished, set status to pending
        if ($new_status !== 'publish' && $old_status === 'publish') {
            update_post_meta($post->ID, 'testimonial_status', 'pending');
            
            // Also update the custom table if it exists
            global $wpdb;
            $table_name = $wpdb->prefix . 'testimonials_pro';
            
            // Check if table exists
            if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
                $wpdb->update(
                    $table_name,
                    array('status' => 'pending'),
                    array('name' => $post->post_title),
                    array('%s'),
                    array('%s')
                );
            }
        }
    }
    
    
    public function deactivate() {
        // Clean up if needed
    }
}

// Initialize the plugin
new TestimonialsPro();

