<?php
/**
 * Frontend class
 */

if (!defined('ABSPATH')) {
    exit;
}

class TestimonialsPro_Frontend {
    
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    public function enqueue_scripts() {
        // Only enqueue on pages with testimonials
        if (is_singular() && (has_shortcode(get_post()->post_content, 'testimonial_form') || 
                             has_shortcode(get_post()->post_content, 'testimonial_grid'))) {
            
            wp_enqueue_script('jquery');
            
            // Enqueue custom script
            wp_enqueue_script(
                'testimonials-pro-frontend',
                TESTIMONIALS_PRO_PLUGIN_URL . 'assets/js/frontend.js',
                array('jquery'),
                TESTIMONIALS_PRO_VERSION,
                true
            );
            
            // Localize script
            wp_localize_script('testimonials-pro-frontend', 'testimonials_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('testimonial_submit')
            ));
            
            // Enqueue styles
            wp_enqueue_style(
                'testimonials-pro-frontend',
                TESTIMONIALS_PRO_PLUGIN_URL . 'assets/css/frontend.css',
                array(),
                TESTIMONIALS_PRO_VERSION
            );
            
            // Add dynamic CSS based on settings
            add_action('wp_head', array($this, 'add_dynamic_css'));
        }
    }
    
    public function add_dynamic_css() {
        $options = get_option('testimonials_pro_options');
        
        // Get colors (prioritize hex inputs over color pickers)
        $button_color = isset($options['button_color_hex']) ? $options['button_color_hex'] : (isset($options['button_color']) ? $options['button_color'] : '#0073aa');
        $button_hover_color = isset($options['button_hover_color_hex']) ? $options['button_hover_color_hex'] : (isset($options['button_hover_color']) ? $options['button_hover_color'] : '#005a87');
        $form_bg_color = isset($options['form_bg_color_hex']) ? $options['form_bg_color_hex'] : (isset($options['form_bg_color']) ? $options['form_bg_color'] : '#f9f9f9');
        
        $grid_columns = isset($options['grid_columns']) ? $options['grid_columns'] : '3';
        
        // Get form width settings
        $form_width = isset($options['form_width']) ? $options['form_width'] : 'fixed';
        $form_width_value = isset($options['form_width_value']) ? $options['form_width_value'] : '600px';
        
        // Determine form width CSS
        $form_width_css = '';
        switch ($form_width) {
            case 'container':
                $form_width_css = 'width: 100%; max-width: none;';
                break;
            case 'custom':
                $form_width_css = 'width: ' . esc_attr($form_width_value) . '; max-width: none;';
                break;
            case 'fixed':
            default:
                $form_width_css = 'max-width: 600px;';
                break;
        }
        
        ?>
        <style type="text/css">
            #testimonial-form-container {
                background: <?php echo esc_attr($form_bg_color); ?> !important;
                <?php echo $form_width_css; ?>
            }
            
            #testimonial-form button {
                background: <?php echo esc_attr($button_color); ?> !important;
            }
            
            #testimonial-form button:hover {
                background: <?php echo esc_attr($button_hover_color); ?> !important;
            }
            
            #testimonials-grid {
                grid-template-columns: repeat(<?php echo esc_attr($grid_columns); ?>, 1fr) !important;
            }
            
            @media (max-width: 768px) {
                #testimonials-grid {
                    grid-template-columns: 1fr !important;
                }
                
                #testimonial-form-container {
                    width: 100% !important;
                    max-width: none !important;
                }
            }
        </style>
        <?php
    }
}