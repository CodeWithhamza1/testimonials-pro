/**
 * Testimonials Pro Admin JavaScript
 */

(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        TestimonialsProAdmin.init();
    });

    // Main TestimonialsProAdmin object
    window.TestimonialsProAdmin = {
        
        init: function() {
            this.initBulkActions();
            this.initQuickActions();
            this.initAnalytics();
            this.initImportExport();
            this.initSettings();
        },

        initBulkActions: function() {
            // Handle bulk action form submission
            $('#bulk-action-selector-top, #bulk-action-selector-bottom').on('change', function() {
                var action = $(this).val();
                if (action && action !== '-1') {
                    $('#doaction, #doaction2').click(function(e) {
                        e.preventDefault();
                        
                        var selectedPosts = $('input[name="post[]"]:checked');
                        if (selectedPosts.length === 0) {
                            alert(testimonialsProAdmin.strings.bulkDeleteConfirm);
                            return;
                        }
                        
                        var confirmMessage = '';
                        switch (action) {
                            case 'approve':
                                confirmMessage = testimonialsProAdmin.strings.bulkApproveConfirm;
                                break;
                            case 'reject':
                                confirmMessage = testimonialsProAdmin.strings.bulkRejectConfirm;
                                break;
                            case 'delete':
                                confirmMessage = testimonialsProAdmin.strings.bulkDeleteConfirm;
                                break;
                        }
                        
                        if (confirm(confirmMessage)) {
                            TestimonialsProAdmin.performBulkAction(action, selectedPosts);
                        }
                    });
                }
            });
        },

        performBulkAction: function(action, $posts) {
            var postIds = [];
            $posts.each(function() {
                postIds.push($(this).val());
            });
            
            $.ajax({
                url: testimonialsProAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'testimonials_bulk_action',
                    nonce: testimonialsProAdmin.nonce,
                    bulk_action: action,
                    post_ids: postIds
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data.message);
                    }
                },
                error: function() {
                    alert('An error occurred while performing the bulk action.');
                }
            });
        },

        initQuickActions: function() {
            // Handle quick approve/reject actions
            $('.testimonial-approve, .testimonial-reject').on('click', function(e) {
                e.preventDefault();
                
                var $link = $(this);
                var action = $link.hasClass('testimonial-approve') ? 'approve' : 'reject';
                var postId = $link.data('id');
                
                if (confirm('Are you sure you want to ' + action + ' this testimonial?')) {
                    $link.closest('tr').addClass('testimonials-pro-loading');
                    
                    $.ajax({
                        url: $link.attr('href'),
                        type: 'GET',
                        success: function() {
                            location.reload();
                        },
                        error: function() {
                            alert('An error occurred while ' + action + 'ing the testimonial.');
                            $link.closest('tr').removeClass('testimonials-pro-loading');
                        }
                    });
                }
            });
        },

        initAnalytics: function() {
            // Initialize charts if Chart.js is available
            if (typeof Chart !== 'undefined') {
                this.initSubmissionChart();
                this.initRatingChart();
            }
            
            // Handle period filter changes
            $('#period').on('change', function() {
                var period = $(this).val();
                TestimonialsProAdmin.loadAnalyticsData(period);
            });
        },

        initSubmissionChart: function() {
            var ctx = document.getElementById('submissionChart');
            if (!ctx) return;
            
            // Chart will be initialized with data from PHP
        },

        initRatingChart: function() {
            var ctx = document.getElementById('ratingChart');
            if (!ctx) return;
            
            // Chart will be initialized with data from PHP
        },

        loadAnalyticsData: function(period) {
            $.ajax({
                url: testimonialsProAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'get_testimonials_analytics',
                    nonce: testimonialsProAdmin.nonce,
                    period: period
                },
                success: function(response) {
                    if (response.success) {
                        TestimonialsProAdmin.updateAnalyticsDisplay(response.data);
                    }
                },
                error: function() {
                    console.error('Failed to load analytics data');
                }
            });
        },

        updateAnalyticsDisplay: function(data) {
            // Update overview cards
            $('.analytics-card').each(function() {
                var $card = $(this);
                var type = $card.find('h3').text().toLowerCase();
                
                switch (type) {
                    case 'total testimonials':
                        $card.find('.analytics-number').text(data.total_testimonials);
                        break;
                    case 'approved':
                        $card.find('.analytics-number').text(data.approved_testimonials);
                        break;
                    case 'pending':
                        $card.find('.analytics-number').text(data.pending_testimonials);
                        break;
                    case 'average rating':
                        $card.find('.analytics-number').text(data.average_rating + '/5');
                        break;
                }
            });
            
            // Update charts
            if (typeof Chart !== 'undefined') {
                TestimonialsProAdmin.updateCharts(data);
            }
        },

        updateCharts: function(data) {
            // Update submission trends chart
            var submissionChart = Chart.getChart('submissionChart');
            if (submissionChart) {
                submissionChart.data.labels = data.submission_trends.labels;
                submissionChart.data.datasets[0].data = data.submission_trends.data;
                submissionChart.update();
            }
            
            // Update rating distribution chart
            var ratingChart = Chart.getChart('ratingChart');
            if (ratingChart) {
                ratingChart.data.labels = data.rating_distribution.labels;
                ratingChart.data.datasets[0].data = data.rating_distribution.data;
                ratingChart.update();
            }
        },

        initImportExport: function() {
            // Handle export form submission
            $('input[name="export_testimonials"]').on('click', function(e) {
                var $form = $(this).closest('form');
                var selectedStatuses = $form.find('input[name="export_status[]"]:checked');
                
                if (selectedStatuses.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one status to export.');
                    return false;
                }
            });
            
            // Handle import form submission
            $('input[name="import_testimonials"]').on('click', function(e) {
                var $form = $(this).closest('form');
                var fileInput = $form.find('input[type="file"]')[0];
                
                if (!fileInput.files.length) {
                    e.preventDefault();
                    alert('Please select a CSV file to import.');
                    return false;
                }
                
                if (!confirm('Are you sure you want to import testimonials? This action cannot be undone.')) {
                    e.preventDefault();
                    return false;
                }
            });
        },

        initSettings: function() {
            // Initialize color pickers
            if ($.fn.wpColorPicker) {
                $('.color-picker').wpColorPicker({
                    change: function(event, ui) {
                        TestimonialsProAdmin.updatePreview();
                    }
                });
            }
            
            // Update preview on settings change
            $('input, select, textarea').on('change', function() {
                TestimonialsProAdmin.updatePreview();
            });
            
            // Handle settings form submission
            $('#testimonials-pro-settings-form').on('submit', function() {
                $('.testimonials-pro-settings').addClass('settings-loading');
            });
        },

        updatePreview: function() {
            // Get current settings values
            var settings = {};
            
            $('input, select, textarea').each(function() {
                var $field = $(this);
                var name = $field.attr('name');
                var value = $field.val();
                
                if (name && value) {
                    var keys = name.replace('testimonials_pro_settings[', '').replace(']', '').split('][');
                    TestimonialsProAdmin.setNestedValue(settings, keys, value);
                }
            });
            
            // Apply preview styles
            TestimonialsProAdmin.applyPreviewStyles(settings);
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

        // Utility functions
        showNotice: function(message, type) {
            type = type || 'info';
            var $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
            $('.wrap h1').after($notice);
            
            setTimeout(function() {
                $notice.fadeOut();
            }, 5000);
        },

        confirmAction: function(message, callback) {
            if (confirm(message)) {
                callback();
            }
        }
    };

    // Add loading state styles
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .testimonials-pro-loading {
                opacity: 0.6;
                pointer-events: none;
                position: relative;
            }
            .testimonials-pro-loading::after {
                content: "";
                position: absolute;
                top: 50%;
                left: 50%;
                width: 20px;
                height: 20px;
                margin: -10px 0 0 -10px;
                border: 2px solid #f3f3f3;
                border-top: 2px solid #0073aa;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }
            .settings-loading {
                opacity: 0.6;
                pointer-events: none;
            }
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        `)
        .appendTo('head');

})(jQuery);
