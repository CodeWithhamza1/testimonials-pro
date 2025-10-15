<?php
/**
 * AJAX handler class
 */

if (!defined('ABSPATH')) {
    exit;
}

class TestimonialsPro_Ajax {
    
    public function __construct() {
        add_action('wp_ajax_submit_testimonial', array($this, 'handle_testimonial_submission'));
        add_action('wp_ajax_nopriv_submit_testimonial', array($this, 'handle_testimonial_submission'));
    }
    
    public function handle_testimonial_submission() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['testimonial_nonce'], 'testimonial_submit')) {
            wp_send_json_error('Security check failed');
        }
        
        // Sanitize input
        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $title = sanitize_text_field($_POST['title']);
        $rating = intval($_POST['rating']);
        $review = sanitize_textarea_field($_POST['review']);
        
        // Validate required fields
        if (empty($name) || empty($email) || empty($review)) {
            wp_send_json_error('Please fill in all required fields');
        }
        
        if ($rating < 1 || $rating > 5) {
            wp_send_json_error('Please select a valid rating');
        }
        
        // Create WordPress post
        $post_data = array(
            'post_title' => $name,
            'post_content' => $review,
            'post_status' => 'pending',
            'post_type' => 'testimonial',
            'post_author' => 1
        );
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id) || $post_id === 0) {
            wp_send_json_error('Failed to submit testimonial');
        }
        
        // Add custom fields
        update_post_meta($post_id, 'testimonial_email', $email);
        update_post_meta($post_id, 'testimonial_title', $title);
        update_post_meta($post_id, 'testimonial_rating', $rating);
        update_post_meta($post_id, 'testimonial_status', 'pending');
        update_post_meta($post_id, 'testimonial_submitted_at', current_time('mysql'));
        
        // Also insert into custom table for compatibility
        global $wpdb;
        $table_name = $wpdb->prefix . 'testimonials_pro';
        
        $wpdb->insert(
            $table_name,
            array(
                'name' => $name,
                'email' => $email,
                'title' => $title,
                'rating' => $rating,
                'review' => $review,
                'status' => 'pending',
                'submitted_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%d', '%s', '%s', '%s')
        );
        
        wp_send_json_success('Testimonial submitted successfully! It will be reviewed before being published.');
    }
}