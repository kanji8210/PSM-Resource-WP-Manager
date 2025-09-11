<?php
/**
 * Archive template for resources
 * This will be used by WordPress when viewing the resource archive
 */

get_header(); ?>

<div class="psm-resources-archive">
    <header class="page-header">
        <h1 class="page-title"><?php _e('Resources', 'psm-resource-manager'); ?></h1>
        <?php if (is_tax('psm_resource_type')): ?>
            <p class="taxonomy-description">
                <?php printf(__('Resources of type: %s', 'psm-resource-manager'), '<strong>' . single_term_title('', false) . '</strong>'); ?>
            </p>
        <?php endif; ?>
    </header>
    
    <!-- Filters -->
    <div class="psm-resources-filters">
        <form class="psm-filter-form">
            <div class="psm-filter-row">
                <div class="psm-filter-group">
                    <label for="resource_type_filter"><?php _e('Filter by Type', 'psm-resource-manager'); ?></label>
                    <select name="resource_type_filter" id="resource_type_filter">
                        <option value=""><?php _e('All Types', 'psm-resource-manager'); ?></option>
                        <?php
                        $resource_types = get_terms(array(
                            'taxonomy' => 'psm_resource_type',
                            'hide_empty' => false,
                        ));
                        foreach ($resource_types as $type):
                        ?>
                            <option value="<?php echo esc_attr($type->slug); ?>">
                                <?php echo esc_html($type->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="psm-filter-group">
                    <label for="resource_search"><?php _e('Search Resources', 'psm-resource-manager'); ?></label>
                    <input type="text" name="resource_search" id="resource_search" placeholder="<?php _e('Search...', 'psm-resource-manager'); ?>">
                </div>
                
                <button type="button" class="psm-filter-button"><?php _e('Filter', 'psm-resource-manager'); ?></button>
                <button type="button" class="psm-filter-reset"><?php _e('Reset', 'psm-resource-manager'); ?></button>
            </div>
        </form>
    </div>
    
    <div class="psm-resources-grid">
        <?php if (have_posts()): ?>
            <?php while (have_posts()): the_post(); ?>
                <?php
                global $wpdb;
                $table_name = $wpdb->prefix . 'psm_resources';
                $resource_data = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM $table_name WHERE post_id = %d",
                    get_the_ID()
                ));
                
                $resource = array(
                    'id' => $resource_data ? $resource_data->id : 0,
                    'title' => get_the_title(),
                    'content' => get_the_content(),
                    'excerpt' => get_the_excerpt(),
                    'type' => $resource_data ? $resource_data->resource_type : 'article',
                    'url' => $resource_data ? $resource_data->url : '',
                    'hosting_option' => $resource_data ? $resource_data->hosting_option : '',
                    'image_id' => $resource_data ? $resource_data->image_id : get_post_thumbnail_id(),
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
        <?php else: ?>
            <div class="psm-no-resources">
                <p><?php _e('No resources found.', 'psm-resource-manager'); ?></p>
            </div>
        <?php endif; ?>
    </div>
    
    <?php
    // Pagination
    the_posts_pagination(array(
        'prev_text' => __('Previous', 'psm-resource-manager'),
        'next_text' => __('Next', 'psm-resource-manager'),
    ));
    ?>
</div>

<script>
// Localize script for frontend functionality
var psm_frontend = {
    ajax_url: '<?php echo admin_url('admin-ajax.php'); ?>',
    nonce: '<?php echo wp_create_nonce('psm_frontend_nonce'); ?>'
};
</script>

<?php get_footer(); ?>