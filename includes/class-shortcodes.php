<?php
/**
 * Shortcodes class
 */

if (!defined('ABSPATH')) {
    exit;
}

class TestimonialsPro_Shortcodes {
    
    public function __construct() {
        add_shortcode('testimonial_form', array($this, 'testimonial_form_shortcode'));
        add_shortcode('testimonial_grid', array($this, 'testimonial_grid_shortcode'));
    }
    
    public function testimonial_form_shortcode($atts) {
        $atts = shortcode_atts(array(), $atts);
        
        ob_start();
        ?>
        <div id="testimonial-form-container">
            <form id="testimonial-form" method="post">
                <?php wp_nonce_field('testimonial_submit', 'testimonial_nonce'); ?>
                
                <div class="form-group">
                    <label for="testimonial_name">Full Name *</label>
                    <input type="text" id="testimonial_name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="testimonial_email">Email *</label>
                    <input type="email" id="testimonial_email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="testimonial_title">Title/Position</label>
                    <input type="text" id="testimonial_title" name="title">
                </div>
                
                <div class="form-group">
                    <label for="testimonial_rating">Rating *</label>
                    <div class="star-rating" data-rating="0">
                        <span class="star" data-value="1">★</span>
                        <span class="star" data-value="2">★</span>
                        <span class="star" data-value="3">★</span>
                        <span class="star" data-value="4">★</span>
                        <span class="star" data-value="5">★</span>
                    </div>
                    <input type="hidden" id="testimonial_rating" name="rating" value="0" required>
                    <div class="rating-text">Click stars to rate</div>
                </div>
                
                <div class="form-group">
                    <label for="testimonial_review">Review *</label>
                    <textarea id="testimonial_review" name="review" rows="5" required></textarea>
                </div>
                
                <button type="submit" id="submit-testimonial">Submit Testimonial</button>
                
                <div id="testimonial-message"></div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function testimonial_grid_shortcode($atts) {
        $atts = shortcode_atts(array(
            'view' => 'all',
            'total' => 'all'
        ), $atts);
        
        // Query database table directly
        global $wpdb;
        $table_name = $wpdb->prefix . 'testimonials_pro';
        
        $where = "WHERE status = 'approved'";
        if ($atts['view'] !== 'all' && is_numeric($atts['view'])) {
            $where .= " AND rating = " . intval($atts['view']);
        }
        
        $all_testimonials = $wpdb->get_results("SELECT * FROM $table_name $where ORDER BY submitted_at DESC");
        
        // Debug information (remove this after testing)
        if (current_user_can('manage_options') && isset($_GET['debug_testimonials'])) {
            echo '<div style="background: #f0f0f0; padding: 10px; margin: 10px 0; border: 1px solid #ccc;">';
            echo '<strong>Debug Info:</strong><br>';
            echo 'Total testimonials found: ' . count($all_testimonials) . '<br>';
            echo 'Query: SELECT * FROM ' . $table_name . ' ' . $where . ' ORDER BY submitted_at DESC<br>';
            if (!empty($all_testimonials)) {
                echo 'First testimonial: ' . $all_testimonials[0]->name . ' (Status: ' . $all_testimonials[0]->status . ')<br>';
            }
            echo '</div>';
        }
        
        // Handle pagination
        $current_page = isset($_GET['testimonial_page']) ? max(1, intval($_GET['testimonial_page'])) : 1;
        $per_page = ($atts['total'] === 'all') ? count($all_testimonials) : intval($atts['total']);
        $total_pages = ($per_page > 0) ? ceil(count($all_testimonials) / $per_page) : 1;
        
        // Get paginated testimonials
        if ($atts['total'] === 'all') {
            $testimonials = $all_testimonials;
        } else {
            $offset = ($current_page - 1) * $per_page;
            $testimonials = array_slice($all_testimonials, $offset, $per_page);
        }
        
        ob_start();
        ?>
        <div class="testimonials-container">
            <div id="testimonials-grid">
                <?php if (empty($testimonials)): ?>
                    <p>No testimonials found.</p>
                <?php else: ?>
                    <?php foreach ($testimonials as $testimonial): ?>
                        <div class="testimonial-item">
                            <div class="testimonial-content">
                                <div class="testimonial-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star <?php echo $i <= $testimonial->rating ? 'filled' : ''; ?>">★</span>
                                    <?php endfor; ?>
                                </div>
                                <p class="testimonial-text">"<?php echo esc_html($testimonial->review); ?>"</p>
                                <div class="testimonial-author">
                                    <strong><?php echo esc_html($testimonial->name); ?></strong>
                                    <?php if ($testimonial->title): ?>
                                        <span class="author-title"><?php echo esc_html($testimonial->title); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <?php if ($total_pages > 1): ?>
                <div class="testimonials-pagination">
                    <?php
                    $base_url = remove_query_arg('testimonial_page');
                    $base_url = add_query_arg('testimonial_page', '%#%', $base_url);
                    
                    echo paginate_links(array(
                        'base' => $base_url,
                        'format' => '',
                        'current' => $current_page,
                        'total' => $total_pages,
                        'prev_text' => '&laquo; Previous',
                        'next_text' => 'Next &raquo;',
                        'type' => 'list'
                    ));
                    ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}