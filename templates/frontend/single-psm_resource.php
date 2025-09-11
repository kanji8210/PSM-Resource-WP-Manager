<?php
/**
 * Single resource template
 */

get_header();

// Get resource data from custom table
global $wpdb;
$table_name = $wpdb->prefix . 'psm_resources';
$resource_data = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM $table_name WHERE post_id = %d",
    get_the_ID()
));
?>

<div class="psm-single-resource" data-resource-id="<?php echo esc_attr($resource_data ? $resource_data->id : 0); ?>">
    <?php while (have_posts()): the_post(); ?>
        <header class="psm-resource-header">
            <?php if ($resource_data): ?>
                <?php
                // Get taxonomy terms
                $terms = wp_get_object_terms(get_the_ID(), 'psm_resource_type');
                $type_name = !empty($terms) ? $terms[0]->name : ucfirst(str_replace('-', ' ', $resource_data->resource_type));
                ?>
                <div class="psm-resource-type psm-resource-type-<?php echo esc_attr($resource_data->resource_type); ?>">
                    <?php echo esc_html($type_name); ?>
                </div>
            <?php endif; ?>
            
            <h1><?php the_title(); ?></h1>
            
            <div class="psm-resource-date">
                <?php echo get_the_date(); ?>
            </div>
        </header>
        
        <div class="psm-resource-body">
            <?php the_content(); ?>
        </div>
        
        <?php if ($resource_data): ?>
            <div class="psm-resource-attachments">
                <h3><?php _e('Resource Access', 'psm-resource-manager'); ?></h3>
                
                <?php if ($resource_data->resource_type === 'pdf' && $resource_data->image_id): ?>
                    <div class="psm-attachment-item">
                        <div class="psm-attachment-icon">
                            <span class="dashicons dashicons-media-document"></span>
                        </div>
                        <div class="psm-attachment-info">
                            <h4><?php _e('PDF Document', 'psm-resource-manager'); ?></h4>
                            <p><?php _e('Download or view the PDF document', 'psm-resource-manager'); ?></p>
                        </div>
                        <div class="psm-attachment-actions">
                            <?php $image_url = wp_get_attachment_url($resource_data->image_id); ?>
                            <a href="<?php echo esc_url($image_url); ?>" target="_blank" class="psm-attachment-button">
                                <?php _e('View PDF', 'psm-resource-manager'); ?>
                                <span class="dashicons dashicons-external"></span>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (in_array($resource_data->resource_type, array('video', 'podcast')) && $resource_data->url): ?>
                    <div class="psm-attachment-item">
                        <div class="psm-attachment-icon">
                            <span class="dashicons dashicons-<?php echo $resource_data->resource_type === 'video' ? 'video-alt3' : 'microphone'; ?>"></span>
                        </div>
                        <div class="psm-attachment-info">
                            <h4><?php echo esc_html(ucfirst($resource_data->resource_type)); ?></h4>
                            <p>
                                <?php if ($resource_data->hosting_option): ?>
                                    <?php printf(__('Hosted on %s', 'psm-resource-manager'), ucfirst($resource_data->hosting_option)); ?>
                                <?php else: ?>
                                    <?php printf(__('Access the %s content', 'psm-resource-manager'), $resource_data->resource_type); ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="psm-attachment-actions">
                            <a href="<?php echo esc_url($resource_data->url); ?>" target="_blank" class="psm-attachment-button">
                                <?php printf(__('Watch %s', 'psm-resource-manager'), $resource_data->resource_type === 'video' ? 'Video' : 'Podcast'); ?>
                                <span class="dashicons dashicons-external"></span>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Embed media if possible -->
                    <div class="psm-media-embed">
                        <div class="psm-media-url" 
                             data-url="<?php echo esc_attr($resource_data->url); ?>" 
                             data-type="<?php echo esc_attr($resource_data->resource_type); ?>"
                             data-hosting="<?php echo esc_attr($resource_data->hosting_option); ?>">
                            <!-- JavaScript will populate this with embedded content -->
                        </div>
                        <?php if ($resource_data->hosting_option): ?>
                            <div class="hosting-info">
                                <?php printf(__('Content hosted on %s', 'psm-resource-manager'), '<strong>' . ucfirst($resource_data->hosting_option) . '</strong>'); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($resource_data->resource_type === 'link' && $resource_data->url): ?>
                    <div class="psm-attachment-item">
                        <div class="psm-attachment-icon">
                            <span class="dashicons dashicons-admin-links"></span>
                        </div>
                        <div class="psm-attachment-info">
                            <h4><?php _e('External Link', 'psm-resource-manager'); ?></h4>
                            <p><?php echo esc_html($resource_data->url); ?></p>
                        </div>
                        <div class="psm-attachment-actions">
                            <a href="<?php echo esc_url($resource_data->url); ?>" target="_blank" class="psm-attachment-button">
                                <?php _e('Visit Link', 'psm-resource-manager'); ?>
                                <span class="dashicons dashicons-external"></span>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Copy link button -->
                <div class="psm-copy-actions">
                    <button type="button" class="psm-copy-link button" data-link="<?php echo esc_url(get_permalink()); ?>">
                        <?php _e('Copy Link to Resource', 'psm-resource-manager'); ?>
                    </button>
                </div>
            </div>
        <?php endif; ?>
        
        <?php
        // Show related resources of the same type
        if ($resource_data && $resource_data->resource_type):
            $related_args = array(
                'post_type' => 'psm_resource',
                'posts_per_page' => 3,
                'post__not_in' => array(get_the_ID()),
                'tax_query' => array(
                    array(
                        'taxonomy' => 'psm_resource_type',
                        'field' => 'slug',
                        'terms' => $resource_data->resource_type
                    )
                )
            );
            
            $related_query = new WP_Query($related_args);
            
            if ($related_query->have_posts()):
        ?>
            <div class="psm-related-resources">
                <h3><?php _e('Related Resources', 'psm-resource-manager'); ?></h3>
                <div class="psm-resources-grid">
                    <?php while ($related_query->have_posts()): $related_query->the_post(); ?>
                        <?php
                        $related_resource_data = $wpdb->get_row($wpdb->prepare(
                            "SELECT * FROM $table_name WHERE post_id = %d",
                            get_the_ID()
                        ));
                        
                        $resource = array(
                            'id' => $related_resource_data ? $related_resource_data->id : 0,
                            'title' => get_the_title(),
                            'content' => get_the_content(),
                            'excerpt' => get_the_excerpt(),
                            'type' => $related_resource_data ? $related_resource_data->resource_type : 'article',
                            'url' => $related_resource_data ? $related_resource_data->url : '',
                            'hosting_option' => $related_resource_data ? $related_resource_data->hosting_option : '',
                            'image_id' => $related_resource_data ? $related_resource_data->image_id : get_post_thumbnail_id(),
                            'date' => get_the_date('Y-m-d H:i:s'),
                            'permalink' => get_permalink()
                        );
                        
                        // Get featured image
                        if ($resource['image_id']) {
                            $resource['image_url'] = wp_get_attachment_image_url($resource['image_id'], 'medium');
                            $resource['image_alt'] = get_post_meta($resource['image_id'], '_wp_attachment_image_alt', true);
                        }
                        
                        // Get taxonomy terms
                        $terms = wp_get_object_terms(get_the_ID(), 'psm_resource_type');
                        $resource['type_name'] = !empty($terms) ? $terms[0]->name : ucfirst(str_replace('-', ' ', $resource['type']));
                        
                        include PSM_RESOURCE_MANAGER_PLUGIN_DIR . 'templates/frontend/resource-card.php';
                        ?>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php
            wp_reset_postdata();
            endif;
        endif;
        ?>
    <?php endwhile; ?>
</div>

<script>
// Localize script for frontend functionality
var psm_frontend = {
    ajax_url: '<?php echo admin_url('admin-ajax.php'); ?>',
    nonce: '<?php echo wp_create_nonce('psm_frontend_nonce'); ?>'
};
</script>

<?php get_footer(); ?>