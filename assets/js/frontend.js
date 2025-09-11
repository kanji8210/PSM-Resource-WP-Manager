jQuery(document).ready(function($) {
    // Filter functionality for resource archive
    $('.psm-filter-button').on('click', function(e) {
        e.preventDefault();
        
        var typeFilter = $('select[name="resource_type_filter"]').val();
        var searchTerm = $('input[name="resource_search"]').val();
        
        // Show loading state
        $('.psm-resources-grid').html('<div class="psm-loading"><span class="psm-spinner"></span>Loading resources...</div>');
        
        // AJAX request to filter resources
        $.ajax({
            url: psm_frontend.ajax_url,
            type: 'POST',
            data: {
                action: 'psm_filter_resources',
                nonce: psm_frontend.nonce,
                resource_type: typeFilter,
                search: searchTerm
            },
            success: function(response) {
                if (response.success) {
                    $('.psm-resources-grid').html(response.data.html);
                } else {
                    $('.psm-resources-grid').html('<div class="psm-no-results"><h3>No resources found</h3><p>Try adjusting your search criteria.</p></div>');
                }
            },
            error: function() {
                $('.psm-resources-grid').html('<div class="psm-no-results"><h3>Error loading resources</h3><p>Please try again later.</p></div>');
            }
        });
    });
    
    // Reset filter
    $('.psm-filter-reset').on('click', function(e) {
        e.preventDefault();
        $('select[name="resource_type_filter"]').val('');
        $('input[name="resource_search"]').val('');
        $('.psm-filter-button').trigger('click');
    });
    
    // Handle video/audio embeds
    $('.psm-media-url').each(function() {
        var url = $(this).data('url');
        var type = $(this).data('type');
        var hosting = $(this).data('hosting');
        
        if (type === 'video') {
            handleVideoEmbed($(this), url, hosting);
        } else if (type === 'podcast') {
            handlePodcastEmbed($(this), url, hosting);
        }
    });
    
    function handleVideoEmbed(element, url, hosting) {
        var embedHtml = '';
        
        if (hosting === 'youtube') {
            var videoId = extractYouTubeId(url);
            if (videoId) {
                embedHtml = '<iframe width="560" height="315" src="https://www.youtube.com/embed/' + videoId + '" frameborder="0" allowfullscreen></iframe>';
            }
        } else if (hosting === 'vimeo') {
            var videoId = extractVimeoId(url);
            if (videoId) {
                embedHtml = '<iframe src="https://player.vimeo.com/video/' + videoId + '" width="560" height="315" frameborder="0" allowfullscreen></iframe>';
            }
        } else {
            // Generic video element
            embedHtml = '<video controls width="560"><source src="' + url + '" type="video/mp4">Your browser does not support the video tag.</video>';
        }
        
        if (embedHtml) {
            element.html(embedHtml);
        } else {
            element.html('<p><a href="' + url + '" target="_blank" class="psm-resource-link">Watch Video <span class="dashicons dashicons-external"></span></a></p>');
        }
    }
    
    function handlePodcastEmbed(element, url, hosting) {
        var embedHtml = '';
        
        if (hosting === 'spotify') {
            var episodeId = extractSpotifyId(url);
            if (episodeId) {
                embedHtml = '<iframe src="https://open.spotify.com/embed/episode/' + episodeId + '" width="100%" height="152" frameborder="0"></iframe>';
            }
        } else if (hosting === 'soundcloud') {
            embedHtml = '<iframe width="100%" height="166" scrolling="no" frameborder="no" src="https://w.soundcloud.com/player/?url=' + encodeURIComponent(url) + '"></iframe>';
        } else {
            // Generic audio element
            embedHtml = '<audio controls><source src="' + url + '" type="audio/mpeg">Your browser does not support the audio element.</audio>';
        }
        
        if (embedHtml) {
            element.html(embedHtml);
        } else {
            element.html('<p><a href="' + url + '" target="_blank" class="psm-resource-link">Listen to Podcast <span class="dashicons dashicons-external"></span></a></p>');
        }
    }
    
    // Helper functions for extracting IDs from URLs
    function extractYouTubeId(url) {
        var regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
        var match = url.match(regExp);
        return (match && match[2].length === 11) ? match[2] : null;
    }
    
    function extractVimeoId(url) {
        var regExp = /(?:vimeo)\.com.*(?:videos|video|channels|)\/([\d]+)/i;
        var match = url.match(regExp);
        return match ? match[1] : null;
    }
    
    function extractSpotifyId(url) {
        var regExp = /spotify\.com\/episode\/([a-zA-Z0-9]+)/;
        var match = url.match(regExp);
        return match ? match[1] : null;
    }
    
    // Smooth scroll for anchor links
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        var target = $($(this).attr('href'));
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 100
            }, 300);
        }
    });
    
    // Lazy loading for images
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
    
    // Track resource interactions for analytics
    $('.psm-resource-link, .psm-attachment-button').on('click', function() {
        var resourceId = $(this).closest('.psm-resource-card, .psm-single-resource').data('resource-id');
        var actionType = $(this).hasClass('psm-attachment-button') ? 'download' : 'view';
        
        // Send analytics data
        $.ajax({
            url: psm_frontend.ajax_url,
            type: 'POST',
            data: {
                action: 'psm_track_interaction',
                nonce: psm_frontend.nonce,
                resource_id: resourceId,
                action_type: actionType
            }
        });
    });
    
    // Enhanced keyboard navigation
    $('.psm-resource-card').on('keypress', function(e) {
        if (e.which === 13 || e.which === 32) { // Enter or Space
            e.preventDefault();
            $(this).find('.psm-resource-title a')[0].click();
        }
    });
    
    // Copy link functionality
    $('.psm-copy-link').on('click', function(e) {
        e.preventDefault();
        var link = $(this).data('link');
        
        if (navigator.clipboard) {
            navigator.clipboard.writeText(link).then(function() {
                showNotification('Link copied to clipboard!', 'success');
            });
        } else {
            // Fallback for older browsers
            var textArea = document.createElement('textarea');
            textArea.value = link;
            document.body.appendChild(textArea);
            textArea.select();
            try {
                document.execCommand('copy');
                showNotification('Link copied to clipboard!', 'success');
            } catch (err) {
                showNotification('Failed to copy link', 'error');
            }
            document.body.removeChild(textArea);
        }
    });
    
    // Show notification helper
    function showNotification(message, type) {
        var notification = $('<div class="psm-notification psm-notification-' + type + '">' + message + '</div>');
        $('body').append(notification);
        
        setTimeout(function() {
            notification.addClass('show');
        }, 100);
        
        setTimeout(function() {
            notification.removeClass('show');
            setTimeout(function() {
                notification.remove();
            }, 300);
        }, 3000);
    }
    
    // Search on enter
    $('input[name="resource_search"]').on('keypress', function(e) {
        if (e.which === 13) {
            $('.psm-filter-button').trigger('click');
        }
    });
});