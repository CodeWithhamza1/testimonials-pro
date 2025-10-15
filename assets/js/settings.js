/**
 * Testimonials Pro Settings JavaScript
 */

(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        TestimonialsProSettings.init();
    });

    // Main TestimonialsProSettings object
    window.TestimonialsProSettings = {
        
        init: function() {
            this.initColorPickers();
            this.initPreview();
            this.initTabs();
            this.initFormValidation();
            this.initResetFunctionality();
        },

        initColorPickers: function() {
            // Initialize WordPress color picker
            if ($.fn.wpColorPicker) {
                $('.color-picker').wpColorPicker({
                    change: function(event, ui) {
                        TestimonialsProSettings.updatePreview();
                    },
                    clear: function() {
                        TestimonialsProSettings.updatePreview();
                    }
                });
            }
        },

        initPreview: function() {
            // Update preview on any setting change
            $('input, select, textarea').on('change keyup', function() {
                TestimonialsProSettings.updatePreview();
            });
            
            // Initial preview update
            this.updatePreview();
        },

        updatePreview: function() {
            var settings = this.getCurrentSettings();
            this.applyPreviewStyles(settings);
        },

        getCurrentSettings: function() {
            var settings = {};
            
            $('input, select, textarea').each(function() {
                var $field = $(this);
                var name = $field.attr('name');
                var value = $field.val();
                
                if (name && value !== '') {
                    var keys = name.replace('testimonials_pro_settings[', '').replace(']', '').split('][');
                    this.setNestedValue(settings, keys, value);
                }
            }.bind(this));
            
            return settings;
        },

        setNestedValue: function(obj, keys, value) {
            var current = obj;
            for (var i = 0; i < keys.length - 1; i++) {
                if (!current[keys[i]]) {
                    current[keys[i]] = {};
                }
                current = current[keys[i]];
            }
            current[keys[keys.length - 1]] = value;
        },

        applyPreviewStyles: function(settings) {
            var $preview = $('.preview-card');
            if (!$preview.length) return;
            
            // Reset styles first
            $preview.attr('style', '');
            
            // Apply colors
            if (settings.colors) {
                if (settings.colors.primary) {
                    $preview.css('border-left-color', settings.colors.primary);
                }
                if (settings.colors.background) {
                    $preview.css('background-color', settings.colors.background);
                }
                if (settings.colors.text) {
                    $preview.css('color', settings.colors.text);
                }
                if (settings.colors.stars) {
                    $preview.find('.star.filled').css('color', settings.colors.stars);
                }
            }
            
            // Apply typography
            if (settings.typography) {
                if (settings.typography.font_size) {
                    $preview.css('font-size', settings.typography.font_size);
                }
                if (settings.typography.font_weight) {
                    $preview.css('font-weight', settings.typography.font_weight);
                }
                if (settings.typography.font_style) {
                    $preview.css('font-style', settings.typography.font_style);
                }
            }
            
            // Apply box design
            if (settings.box_design) {
                if (settings.box_design.border_radius) {
                    $preview.css('border-radius', settings.box_design.border_radius);
                }
                if (settings.box_design.padding) {
                    $preview.css('padding', settings.box_design.padding);
                }
                if (settings.box_design.margin) {
                    $preview.css('margin', settings.box_design.margin);
                }
                if (settings.box_design.shadow) {
                    $preview.css('box-shadow', settings.box_design.shadow);
                }
            }
        },

        initTabs: function() {
            // Create tabs if they don't exist
            if (!$('.settings-tabs').length) {
                this.createTabs();
            }
            
            // Handle tab clicks
            $('.settings-tabs a').on('click', function(e) {
                e.preventDefault();
                
                var $tab = $(this);
                var target = $tab.attr('href');
                
                // Update active tab
                $('.settings-tabs a').removeClass('active');
                $tab.addClass('active');
                
                // Show target content
                $('.settings-tab-content').removeClass('active');
                $(target).addClass('active');
            });
        },

        createTabs: function() {
            var $form = $('form[method="post"]');
            var $sections = $form.find('h2');
            
            if ($sections.length <= 1) return;
            
            // Create tabs container
            var $tabsContainer = $('<div class="settings-tabs"><ul></ul></div>');
            var $tabsList = $tabsContainer.find('ul');
            
            // Create tab content containers
            $sections.each(function(index) {
                var $section = $(this);
                var sectionId = 'tab-' + index;
                var sectionTitle = $section.text();
                
                // Create tab link
                var $tabLink = $('<a href="#' + sectionId + '">' + sectionTitle + '</a>');
                if (index === 0) $tabLink.addClass('active');
                
                $tabsList.append($('<li></li>').append($tabLink));
                
                // Wrap section content
                var $sectionContent = $section.nextUntil('h2');
                var $tabContent = $('<div class="settings-tab-content" id="' + sectionId + '"></div>');
                if (index === 0) $tabContent.addClass('active');
                
                $tabContent.append($section).append($sectionContent);
                $form.append($tabContent);
            });
            
            // Insert tabs before form
            $form.before($tabsContainer);
        },

        initFormValidation: function() {
            // Validate email fields
            $('input[type="email"]').on('blur', function() {
                var $field = $(this);
                var email = $field.val();
                
                if (email && !this.isValidEmail(email)) {
                    $field.addClass('error');
                    this.showFieldError($field, 'Please enter a valid email address.');
                } else {
                    $field.removeClass('error');
                    this.hideFieldError($field);
                }
            }.bind(this));
            
            // Validate number fields
            $('input[type="number"]').on('blur', function() {
                var $field = $(this);
                var value = parseInt($field.val());
                var min = parseInt($field.attr('min'));
                var max = parseInt($field.attr('max'));
                
                if (value < min || value > max) {
                    $field.addClass('error');
                    this.showFieldError($field, 'Value must be between ' + min + ' and ' + max + '.');
                } else {
                    $field.removeClass('error');
                    this.hideFieldError($field);
                }
            }.bind(this));
        },

        isValidEmail: function(email) {
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        },

        showFieldError: function($field, message) {
            this.hideFieldError($field);
            $field.after('<div class="field-error">' + message + '</div>');
        },

        hideFieldError: function($field) {
            $field.siblings('.field-error').remove();
        },

        initResetFunctionality: function() {
            // Add reset button if it doesn't exist
            if (!$('.reset-settings').length) {
                var $resetBtn = $('<button type="button" class="reset-settings">Reset to Defaults</button>');
                $('.submit').after($resetBtn);
            }
            
            // Handle reset button click
            $('.reset-settings').on('click', function() {
                if (confirm('Are you sure you want to reset all settings to their default values? This action cannot be undone.')) {
                    this.resetToDefaults();
                }
            }.bind(this));
        },

        resetToDefaults: function() {
            // Reset form fields to default values
            $('input[type="text"], input[type="email"], input[type="number"]').each(function() {
                var $field = $(this);
                var defaultValue = $field.data('default');
                if (defaultValue !== undefined) {
                    $field.val(defaultValue);
                }
            });
            
            $('select').each(function() {
                var $field = $(this);
                var defaultValue = $field.data('default');
                if (defaultValue !== undefined) {
                    $field.val(defaultValue);
                }
            });
            
            $('input[type="checkbox"]').each(function() {
                var $field = $(this);
                var defaultValue = $field.data('default');
                if (defaultValue !== undefined) {
                    $field.prop('checked', defaultValue === '1' || defaultValue === true);
                }
            });
            
            // Update preview
            this.updatePreview();
            
            // Show success message
            this.showNotice('Settings have been reset to default values.', 'success');
        },

        showNotice: function(message, type) {
            type = type || 'info';
            var $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
            $('.wrap h1').after($notice);
            
            setTimeout(function() {
                $notice.fadeOut();
            }, 5000);
        },

        // Export settings
        exportSettings: function() {
            var settings = this.getCurrentSettings();
            var dataStr = JSON.stringify(settings, null, 2);
            var dataBlob = new Blob([dataStr], {type: 'application/json'});
            
            var link = document.createElement('a');
            link.href = URL.createObjectURL(dataBlob);
            link.download = 'testimonials-pro-settings.json';
            link.click();
        },

        // Import settings
        importSettings: function(file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                try {
                    var settings = JSON.parse(e.target.result);
                    TestimonialsProSettings.applyImportedSettings(settings);
                } catch (error) {
                    TestimonialsProSettings.showNotice('Invalid settings file.', 'error');
                }
            };
            reader.readAsText(file);
        },

        applyImportedSettings: function(settings) {
            // Apply imported settings to form fields
            for (var key in settings) {
                if (settings.hasOwnProperty(key)) {
                    this.applySettingValue(key, settings[key]);
                }
            }
            
            // Update preview
            this.updatePreview();
            
            // Show success message
            this.showNotice('Settings have been imported successfully.', 'success');
        },

        applySettingValue: function(key, value) {
            if (typeof value === 'object') {
                for (var subKey in value) {
                    if (value.hasOwnProperty(subKey)) {
                        this.applySettingValue(key + '.' + subKey, value[subKey]);
                    }
                }
            } else {
                var $field = $('input[name="testimonials_pro_settings[' + key + ']"], select[name="testimonials_pro_settings[' + key + ']"]');
                if ($field.length) {
                    if ($field.is(':checkbox')) {
                        $field.prop('checked', value === '1' || value === true);
                    } else {
                        $field.val(value);
                    }
                }
            }
        }
    };

    // Add CSS for form validation
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .field-error {
                color: #e74c3c;
                font-size: 12px;
                margin-top: 5px;
                display: block;
            }
            input.error, select.error {
                border-color: #e74c3c !important;
                box-shadow: 0 0 0 2px rgba(231, 76, 60, 0.2);
            }
            .settings-tabs {
                margin-bottom: 20px;
                border-bottom: 1px solid #ddd;
            }
            .settings-tabs ul {
                margin: 0;
                padding: 0;
                list-style: none;
                display: flex;
            }
            .settings-tabs li {
                margin: 0;
            }
            .settings-tabs a {
                display: block;
                padding: 10px 20px;
                text-decoration: none;
                color: #666;
                border-bottom: 2px solid transparent;
                transition: all 0.3s ease;
            }
            .settings-tabs a:hover,
            .settings-tabs a.active {
                color: #0073aa;
                border-bottom-color: #0073aa;
            }
            .settings-tab-content {
                display: none;
            }
            .settings-tab-content.active {
                display: block;
            }
        `)
        .appendTo('head');

})(jQuery);
