<?php
/**
 * Admin class for managing testimonials
 */

if (!defined('ABSPATH')) {
    exit;
}

class TestimonialsPro_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_approve_testimonial', array($this, 'approve_testimonial'));
        add_action('wp_ajax_delete_testimonial', array($this, 'delete_testimonial'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'All Testimonials',
            'All Testimonials',
            'manage_options',
            'testimonials-admin',
            array($this, 'admin_page'),
            'dashicons-format-quote',
            25
        );
    }
    
    public function enqueue_admin_scripts($hook) {
        if ($hook === 'toplevel_page_testimonials-admin') {
            wp_enqueue_script('jquery');
            wp_enqueue_style('testimonials-admin', TESTIMONIALS_PRO_PLUGIN_URL . 'assets/css/admin.css', array(), TESTIMONIALS_PRO_VERSION);
        }
    }
    
    public function admin_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'testimonials_pro';
        
        // Handle actions
        if (isset($_GET['action']) && isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $action = sanitize_text_field($_GET['action']);
            
            if ($action === 'approve') {
                // Update custom table
                $wpdb->update($table_name, array('status' => 'approved'), array('id' => $id));
                
                // Also update WordPress post if it exists
                $testimonial = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));
                if ($testimonial) {
                    $posts = get_posts(array(
                        'post_type' => 'testimonial',
                        'post_title' => $testimonial->name,
                        'posts_per_page' => 1
                    ));
                    
                    if (!empty($posts)) {
                        wp_update_post(array(
                            'ID' => $posts[0]->ID,
                            'post_status' => 'publish'
                        ));
                        update_post_meta($posts[0]->ID, 'testimonial_status', 'approved');
                    }
                }
                
                echo '<div class="notice notice-success"><p>Testimonial approved!</p></div>';
            } elseif ($action === 'reject') {
                // Update custom table
                $wpdb->update($table_name, array('status' => 'rejected'), array('id' => $id));
                
                // Also update WordPress post if it exists
                $testimonial = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));
                if ($testimonial) {
                    $posts = get_posts(array(
                        'post_type' => 'testimonial',
                        'post_title' => $testimonial->name,
                        'posts_per_page' => 1
                    ));
                    
                    if (!empty($posts)) {
                        wp_update_post(array(
                            'ID' => $posts[0]->ID,
                            'post_status' => 'draft'
                        ));
                        update_post_meta($posts[0]->ID, 'testimonial_status', 'rejected');
                    }
                }
                
                echo '<div class="notice notice-success"><p>Testimonial rejected!</p></div>';
            } elseif ($action === 'delete') {
                // Get testimonial data before deleting
                $testimonial = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));
                
                // Delete from custom table
                $wpdb->delete($table_name, array('id' => $id));
                
                // Also delete WordPress post if it exists
                if ($testimonial) {
                    $posts = get_posts(array(
                        'post_type' => 'testimonial',
                        'post_title' => $testimonial->name,
                        'posts_per_page' => 1
                    ));
                    
                    if (!empty($posts)) {
                        wp_delete_post($posts[0]->ID, true);
                    }
                }
                
                echo '<div class="notice notice-success"><p>Testimonial deleted!</p></div>';
            }
        }
        
        // Get testimonials
        $testimonials = $wpdb->get_results("SELECT * FROM $table_name ORDER BY submitted_at DESC");
        
        ?>
        <div class="wrap">
            <h1>All Testimonials</h1>
            
            <div class="testimonials-admin">
                <?php if (empty($testimonials)): ?>
                    <p>No testimonials found.</p>
                <?php else: ?>
                    <div class="testimonials-list">
                        <?php foreach ($testimonials as $testimonial): ?>
                            <div class="testimonial-item-admin">
                                <div class="testimonial-header">
                                    <h3><?php echo esc_html($testimonial->name); ?></h3>
                                    <span class="status status-<?php echo esc_attr($testimonial->status); ?>">
                                        <?php echo ucfirst($testimonial->status); ?>
                                    </span>
                                </div>
                                
                                <div class="testimonial-content">
                                    <div class="testimonial-rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span class="star <?php echo $i <= $testimonial->rating ? 'filled' : ''; ?>">★</span>
                                        <?php endfor; ?>
                                    </div>
                                    
                                    <p class="testimonial-text">"<?php echo esc_html($testimonial->review); ?>"</p>
                                    
                                    <div class="testimonial-meta">
                                        <p><strong>Email:</strong> <?php echo esc_html($testimonial->email); ?></p>
                                        <?php if ($testimonial->title): ?>
                                            <p><strong>Title:</strong> <?php echo esc_html($testimonial->title); ?></p>
                                        <?php endif; ?>
                                        <p><strong>Submitted:</strong> <?php echo date('M j, Y g:i A', strtotime($testimonial->submitted_at)); ?></p>
                                    </div>
                                </div>
                                
                                <div class="testimonial-actions">
                                    <?php if ($testimonial->status === 'pending'): ?>
                                        <a href="?page=testimonials-admin&action=approve&id=<?php echo $testimonial->id; ?>" class="button button-primary">Approve</a>
                                        <a href="?page=testimonials-admin&action=reject&id=<?php echo $testimonial->id; ?>" class="button">Reject</a>
                                    <?php elseif ($testimonial->status === 'rejected'): ?>
                                        <a href="?page=testimonials-admin&action=approve&id=<?php echo $testimonial->id; ?>" class="button button-primary">Approve</a>
                                    <?php endif; ?>
                                    <a href="?page=testimonials-admin&action=delete&id=<?php echo $testimonial->id; ?>" class="button button-link-delete" onclick="return confirm('Are you sure you want to delete this testimonial?')">Delete</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}
