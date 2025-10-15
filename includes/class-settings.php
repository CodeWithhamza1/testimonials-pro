<?php
/**
 * Settings class for customization options
 */

if (!defined('ABSPATH')) {
    exit;
}

class TestimonialsPro_Settings {
    
    public function __construct() {
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    public function register_settings() {
        // Register settings
        register_setting('testimonials_pro_settings', 'testimonials_pro_options');
        
        // Add settings section
        add_settings_section(
            'testimonials_pro_main',
            'Customization Options',
            array($this, 'settings_section_callback'),
            'testimonials_pro_settings'
        );
        
        // Button color field
        add_settings_field(
            'button_color',
            'Button Color',
            array($this, 'button_color_callback'),
            'testimonials_pro_settings',
            'testimonials_pro_main'
        );
        
        // Button hover color field
        add_settings_field(
            'button_hover_color',
            'Button Hover Color',
            array($this, 'button_hover_color_callback'),
            'testimonials_pro_settings',
            'testimonials_pro_main'
        );
        
        // Form background color
        add_settings_field(
            'form_bg_color',
            'Form Background Color',
            array($this, 'form_bg_color_callback'),
            'testimonials_pro_settings',
            'testimonials_pro_main'
        );
        
        // Grid columns field
        add_settings_field(
            'grid_columns',
            'Grid Columns (Desktop)',
            array($this, 'grid_columns_callback'),
            'testimonials_pro_settings',
            'testimonials_pro_main'
        );
        
        // Form width field
        add_settings_field(
            'form_width',
            'Form Width',
            array($this, 'form_width_callback'),
            'testimonials_pro_settings',
            'testimonials_pro_main'
        );
        
        // Form width value field
        add_settings_field(
            'form_width_value',
            'Form Width Value',
            array($this, 'form_width_value_callback'),
            'testimonials_pro_settings',
            'testimonials_pro_main'
        );
    }
    
    public function settings_section_callback() {
        echo '<p>Customize the appearance of your testimonials form and grid.</p>';
    }
    
    public function button_color_callback() {
        $options = get_option('testimonials_pro_options');
        $value = isset($options['button_color']) ? $options['button_color'] : '#0073aa';
        echo '<input type="color" name="testimonials_pro_options[button_color]" value="' . esc_attr($value) . '" style="margin-right: 10px;" />';
        echo '<input type="text" name="testimonials_pro_options[button_color_hex]" value="' . esc_attr($value) . '" placeholder="#0073aa" style="width: 100px;" />';
        echo '<p class="description">Choose the color for the submit button. You can use the color picker or enter a hex color directly.</p>';
    }
    
    public function button_hover_color_callback() {
        $options = get_option('testimonials_pro_options');
        $value = isset($options['button_hover_color']) ? $options['button_hover_color'] : '#005a87';
        echo '<input type="color" name="testimonials_pro_options[button_hover_color]" value="' . esc_attr($value) . '" style="margin-right: 10px;" />';
        echo '<input type="text" name="testimonials_pro_options[button_hover_color_hex]" value="' . esc_attr($value) . '" placeholder="#005a87" style="width: 100px;" />';
        echo '<p class="description">Choose the hover color for the submit button. You can use the color picker or enter a hex color directly.</p>';
    }
    
    public function form_bg_color_callback() {
        $options = get_option('testimonials_pro_options');
        $value = isset($options['form_bg_color']) ? $options['form_bg_color'] : '#f9f9f9';
        echo '<input type="color" name="testimonials_pro_options[form_bg_color]" value="' . esc_attr($value) . '" style="margin-right: 10px;" />';
        echo '<input type="text" name="testimonials_pro_options[form_bg_color_hex]" value="' . esc_attr($value) . '" placeholder="#f9f9f9" style="width: 100px;" />';
        echo '<p class="description">Choose the background color for the form container. You can use the color picker or enter a hex color directly.</p>';
    }
    
    public function grid_columns_callback() {
        $options = get_option('testimonials_pro_options');
        $value = isset($options['grid_columns']) ? $options['grid_columns'] : '3';
        echo '<select name="testimonials_pro_options[grid_columns]">';
        echo '<option value="1"' . selected($value, '1', false) . '>1 Column</option>';
        echo '<option value="2"' . selected($value, '2', false) . '>2 Columns</option>';
        echo '<option value="3"' . selected($value, '3', false) . '>3 Columns</option>';
        echo '<option value="4"' . selected($value, '4', false) . '>4 Columns</option>';
        echo '</select>';
        echo '<p class="description">Number of columns for the testimonials grid on desktop.</p>';
    }
    
    public function form_width_callback() {
        $options = get_option('testimonials_pro_options');
        $value = isset($options['form_width']) ? $options['form_width'] : 'fixed';
        echo '<select name="testimonials_pro_options[form_width]" id="form_width_select">';
        echo '<option value="fixed"' . selected($value, 'fixed', false) . '>Fixed Width (600px)</option>';
        echo '<option value="container"' . selected($value, 'container', false) . '>Container Width (100%)</option>';
        echo '<option value="custom"' . selected($value, 'custom', false) . '>Custom Width</option>';
        echo '</select>';
        echo '<p class="description">Choose how the form width should be displayed.</p>';
    }
    
    public function form_width_value_callback() {
        $options = get_option('testimonials_pro_options');
        $value = isset($options['form_width_value']) ? $options['form_width_value'] : '600px';
        echo '<input type="text" name="testimonials_pro_options[form_width_value]" value="' . esc_attr($value) . '" placeholder="600px" style="width: 120px;" id="form_width_value" />';
        echo '<p class="description">Enter custom width (e.g., 500px, 80%, 50vw). Only applies when "Custom Width" is selected above.</p>';
    }
    
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>Testimonials Pro Settings</h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('testimonials_pro_settings');
                do_settings_sections('testimonials_pro_settings');
                submit_button();
                ?>
            </form>
            
            <hr>
            
            <h2>Shortcodes</h2>
            <p><strong>Display Testimonial Form:</strong> <code>[testimonial_form]</code></p>
            <p><strong>Display Testimonials Grid:</strong> <code>[testimonial_grid]</code></p>
            <p><strong>Display Grid with Rating Filter:</strong> <code>[testimonial_grid view="5"]</code></p>
            
            <h3>Grid Parameters:</h3>
            <ul>
                <li><code>view="5"</code> - Show only 5-star testimonials</li>
                <li><code>view="4"</code> - Show only 4-star testimonials</li>
                <li><code>view="3"</code> - Show only 3-star testimonials</li>
                <li><code>view="2"</code> - Show only 2-star testimonials</li>
                <li><code>view="1"</code> - Show only 1-star testimonials</li>
                <li><code>view="all"</code> - Show all testimonials (default)</li>
                <li><code>total="3"</code> - Show 3 testimonials per page with pagination</li>
                <li><code>total="5"</code> - Show 5 testimonials per page with pagination</li>
                <li><code>total="all"</code> - Show all testimonials without pagination (default)</li>
            </ul>
            
            <h3>Examples:</h3>
            <ul>
                <li><code>[testimonial_grid]</code> - Show all testimonials</li>
                <li><code>[testimonial_grid total="3"]</code> - Show 3 testimonials per page</li>
                <li><code>[testimonial_grid view="5" total="2"]</code> - Show 2 five-star testimonials per page</li>
                <li><code>[testimonial_grid view="4" total="4"]</code> - Show 4 four-star testimonials per page</li>
            </ul>
            
            <h2>Usage</h2>
            <p>1. Use <code>[testimonial_form]</code> to show the submission form</p>
            <p>2. Use <code>[testimonial_grid]</code> to display all testimonials</p>
            <p>3. Use <code>[testimonial_grid view="5"]</code> to show only 5-star testimonials</p>
            <p>4. Manage testimonials in the "Testimonials" menu above</p>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            function toggleWidthValue() {
                var widthType = $('#form_width_select').val();
                if (widthType === 'custom') {
                    $('#form_width_value').closest('tr').show();
                } else {
                    $('#form_width_value').closest('tr').hide();
                }
            }
            
            $('#form_width_select').on('change', toggleWidthValue);
            toggleWidthValue(); // Initial call
            
            // Sync color picker with hex input
            $('input[type="color"]').on('change', function() {
                var hexInput = $(this).siblings('input[type="text"]');
                hexInput.val($(this).val());
            });
            
            // Sync hex input with color picker
            $('input[type="text"][placeholder*="#"]').on('input', function() {
                var colorPicker = $(this).siblings('input[type="color"]');
                if ($(this).val().match(/^#[0-9A-Fa-f]{6}$/)) {
                    colorPicker.val($(this).val());
                }
            });
        });
        </script>
        <?php
    }
}
