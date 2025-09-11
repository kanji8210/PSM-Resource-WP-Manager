jQuery(document).ready(function($) {
    // Handle resource type selection and show/hide fields
    $('#resource_type').on('change', function() {
        var selectedType = $(this).val();
        
        // Hide all type-specific fields first
        $('.type-specific-fields').hide();
        
        // Show fields based on selected type
        if (selectedType === 'pdf') {
            $('#pdf-fields').show();
        } else if (selectedType === 'video' || selectedType === 'podcast') {
            $('#video-podcast-fields').show();
            $('#hosting-options-fields').show();
        } else if (selectedType === 'link') {
            $('#link-fields').show();
        }
    });
    
    // Media uploader for images
    var mediaUploader;
    
    $('#upload_image_button').on('click', function(e) {
        e.preventDefault();
        
        // If the uploader object has already been created, reopen the dialog
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        
        // Extend the wp.media object
        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Image',
            button: {
                text: 'Choose Image'
            },
            multiple: false
        });
        
        // When an image is selected, run a callback
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            
            $('#resource_image_id').val(attachment.id);
            $('#resource_image_preview').html(
                '<img src="' + attachment.sizes.thumbnail.url + '" alt="' + attachment.alt + '">'
            );
            
            $('#upload_image_button').text('Change Image');
            $('#remove_image_button').show();
        });
        
        // Open the uploader dialog
        mediaUploader.open();
    });
    
    // Remove image
    $('#remove_image_button').on('click', function(e) {
        e.preventDefault();
        
        $('#resource_image_id').val('');
        $('#resource_image_preview').html('');
        $('#upload_image_button').text('Upload Image');
        $('#remove_image_button').hide();
    });
    
    // Auto-generate slug from title (if needed)
    $('#resource_title').on('input', function() {
        var title = $(this).val();
        // This could be used for auto-generating slugs if needed
    });
    
    // Form validation
    $('#psm-resource-form').on('submit', function(e) {
        var resourceType = $('#resource_type').val();
        var title = $('#resource_title').val().trim();
        
        // Basic validation
        if (!title) {
            alert('Please enter a title for the resource.');
            $('#resource_title').focus();
            e.preventDefault();
            return false;
        }
        
        if (!resourceType) {
            alert('Please select a resource type.');
            $('#resource_type').focus();
            e.preventDefault();
            return false;
        }
        
        // Type-specific validation
        if (resourceType === 'video' || resourceType === 'podcast') {
            var url = $('#resource_url').val().trim();
            if (!url) {
                alert('Please enter a URL for the ' + resourceType + '.');
                $('#resource_url').focus();
                e.preventDefault();
                return false;
            }
        }
        
        if (resourceType === 'link') {
            var externalUrl = $('#external_url').val().trim();
            if (!externalUrl) {
                alert('Please enter an external URL for the link.');
                $('#external_url').focus();
                e.preventDefault();
                return false;
            }
        }
    });
    
    // URL validation helper
    function isValidUrl(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }
    
    // Real-time URL validation
    $('#resource_url, #external_url').on('blur', function() {
        var url = $(this).val().trim();
        if (url && !isValidUrl(url)) {
            $(this).addClass('error');
            $(this).after('<span class="url-error" style="color: red; font-size: 12px;">Please enter a valid URL</span>');
        } else {
            $(this).removeClass('error');
            $(this).siblings('.url-error').remove();
        }
    });
    
    // AJAX functionality for saving resources
    function saveResourceData(formData) {
        $.ajax({
            url: psm_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'psm_save_resource',
                nonce: psm_ajax.nonce,
                form_data: formData
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    $('<div class="notice notice-success is-dismissible"><p>' + response.data.message + '</p></div>')
                        .insertAfter('.wrap h1')
                        .delay(3000)
                        .fadeOut();
                    
                    // Reset form if needed
                    if (response.data.reset_form) {
                        $('#psm-resource-form')[0].reset();
                        $('.type-specific-fields').hide();
                        $('#resource_image_preview').html('');
                        $('#upload_image_button').text('Upload Image');
                        $('#remove_image_button').hide();
                    }
                } else {
                    // Show error message
                    $('<div class="notice notice-error is-dismissible"><p>' + response.data.message + '</p></div>')
                        .insertAfter('.wrap h1')
                        .delay(5000)
                        .fadeOut();
                }
            },
            error: function() {
                $('<div class="notice notice-error is-dismissible"><p>An error occurred while saving the resource.</p></div>')
                    .insertAfter('.wrap h1')
                    .delay(5000)
                    .fadeOut();
            }
        });
    }
    
    // Dismiss notices
    $(document).on('click', '.notice-dismiss', function() {
        $(this).parent('.notice').fadeOut();
    });
    
    // Auto-dismiss success notices
    setTimeout(function() {
        $('.notice-success').fadeOut();
    }, 5000);
});