jQuery(document).ready(function($) {
    // Handle star rating interaction
    $('.star-rating .star').on('click', function() {
        var rating = $(this).data('value');
        var starRating = $(this).parent();
        
        // Update visual stars
        starRating.find('.star').removeClass('active');
        starRating.find('.star').each(function(index) {
            if (index < rating) {
                $(this).addClass('active');
            }
        });
        
        // Update hidden input
        $('#testimonial_rating').val(rating);
        
        // Update rating text
        var ratingTexts = ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];
        $('.rating-text').text(ratingTexts[rating]);
        
        // Update data attribute
        starRating.attr('data-rating', rating);
    });
    
    // Handle star hover effects
    $('.star-rating .star').on('mouseenter', function() {
        var rating = $(this).data('value');
        var starRating = $(this).parent();
        
        starRating.find('.star').removeClass('hover');
        starRating.find('.star').each(function(index) {
            if (index < rating) {
                $(this).addClass('hover');
            }
        });
    });
    
    $('.star-rating').on('mouseleave', function() {
        $(this).find('.star').removeClass('hover');
    });
    
    // Handle "Read more..." / "Read less" toggle
    $(document).on('click', '.read-more-link', function(e) {
        e.preventDefault();
        
        var $this = $(this);
        var $testimonialText = $this.closest('.testimonial-text');
        var $excerpt = $testimonialText.find('.testimonial-excerpt');
        var $full = $testimonialText.find('.testimonial-full');
        
        if ($excerpt.is(':visible')) {
            // Show full text
            $excerpt.hide();
            $full.show();
            $this.text('Read less');
        } else {
            // Show excerpt
            $full.hide();
            $excerpt.show();
            $this.text('Read more...');
        }
    });
    
    // Handle testimonial form submission
    $('#testimonial-form').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var submitBtn = form.find('button[type="submit"]');
        var messageDiv = $('#testimonial-message');
        var rating = $('#testimonial_rating').val();
        
        // Validate rating
        if (rating == '0' || rating == '') {
            messageDiv.removeClass('success').addClass('error').html('<p>Please select a rating by clicking the stars.</p>');
            return;
        }
        
        // Disable submit button
        submitBtn.prop('disabled', true).text('Submitting...');
        
        // Clear previous messages
        messageDiv.removeClass('success error').html('');
        
        $.ajax({
            url: testimonials_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'submit_testimonial',
                testimonial_nonce: testimonials_ajax.nonce,
                name: $('#testimonial_name').val(),
                email: $('#testimonial_email').val(),
                title: $('#testimonial_title').val(),
                rating: rating,
                review: $('#testimonial_review').val()
            },
            success: function(response) {
                if (response.success) {
                    messageDiv.addClass('success').html('<p>' + response.data + '</p>');
                    form[0].reset(); // Reset form
                    // Reset star rating
                    $('.star-rating').attr('data-rating', '0').find('.star').removeClass('active hover');
                    $('.rating-text').text('Click stars to rate');
                } else {
                    messageDiv.addClass('error').html('<p>' + response.data + '</p>');
                }
            },
            error: function() {
                messageDiv.addClass('error').html('<p>An error occurred. Please try again.</p>');
            },
            complete: function() {
                submitBtn.prop('disabled', false).text('Submit Testimonial');
            }
        });
    });
});
