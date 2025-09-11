<?php
/**
 * Template for displaying a single resource card
 * Used in archive and filtered results
 */
?>
<div class="psm-resource-card" data-resource-id="<?php echo esc_attr($resource['id']); ?>">
    <div class="psm-resource-image">
        <?php if (!empty($resource['image_url'])): ?>
            <img src="<?php echo esc_url($resource['image_url']); ?>" 
                 alt="<?php echo esc_attr($resource['image_alt'] ?? $resource['title']); ?>"
                 loading="lazy">
        <?php else: ?>
            <div class="no-image">
                <?php
                $icon = 'dashicons-media-default';
                switch($resource['type']) {
                    case 'pdf': $icon = 'dashicons-media-document'; break;
                    case 'video': $icon = 'dashicons-video-alt3'; break;
                    case 'podcast': $icon = 'dashicons-microphone'; break;
                    case 'article': $icon = 'dashicons-media-text'; break;
                    case 'link': $icon = 'dashicons-admin-links'; break;
                }
                ?>
                <span class="dashicons <?php echo esc_attr($icon); ?>"></span>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="psm-resource-content">
        <div class="psm-resource-type psm-resource-type-<?php echo esc_attr($resource['type']); ?>">
            <?php echo esc_html($resource['type_name']); ?>
        </div>
        
        <h3 class="psm-resource-title">
            <a href="<?php echo esc_url($resource['permalink']); ?>">
                <?php echo esc_html($resource['title']); ?>
            </a>
        </h3>
        
        <?php if (!empty($resource['excerpt'])): ?>
        <div class="psm-resource-excerpt">
            <?php echo wp_trim_words($resource['excerpt'], 20, '...'); ?>
        </div>
        <?php endif; ?>
        
        <div class="psm-resource-meta">
            <div class="psm-resource-date">
                <?php echo date_i18n(get_option('date_format'), strtotime($resource['date'])); ?>
            </div>
            
            <a href="<?php echo esc_url($resource['permalink']); ?>" class="psm-resource-link">
                <?php _e('View Resource', 'psm-resource-manager'); ?>
                <span class="dashicons dashicons-arrow-right-alt"></span>
            </a>
        </div>
    </div>
</div>