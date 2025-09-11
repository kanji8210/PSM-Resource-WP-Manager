<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get resources from custom table and posts
global $wpdb;
$table_name = $wpdb->prefix . 'psm_resources';

// Handle bulk actions
if (isset($_POST['action']) && $_POST['action'] === 'delete_selected' && wp_verify_nonce($_POST['bulk_nonce'], 'bulk_action_resources')) {
    if (!empty($_POST['resource_ids'])) {
        foreach ($_POST['resource_ids'] as $resource_id) {
            $resource_id = intval($resource_id);
            // Delete from custom table
            $wpdb->delete($table_name, array('id' => $resource_id), array('%d'));
            // Also delete the associated post if it exists
            $resource_data = $wpdb->get_row($wpdb->prepare("SELECT post_id FROM $table_name WHERE id = %d", $resource_id));
            if ($resource_data && $resource_data->post_id) {
                wp_delete_post($resource_data->post_id, true);
            }
        }
        echo '<div class="notice notice-success is-dismissible"><p>' . __('Selected resources deleted successfully.', 'psm-resource-manager') . '</p></div>';
    }
}

// Get resources with pagination
$per_page = 20;
$current_page = max(1, intval($_GET['paged'] ?? 1));
$offset = ($current_page - 1) * $per_page;

$resources = $wpdb->get_results($wpdb->prepare(
    "SELECT r.*, p.post_title, p.post_status, p.post_date 
     FROM $table_name r 
     LEFT JOIN {$wpdb->posts} p ON r.post_id = p.ID 
     ORDER BY r.created_at DESC 
     LIMIT %d OFFSET %d",
    $per_page,
    $offset
));

$total_resources = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
$total_pages = ceil($total_resources / $per_page);
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html(get_admin_page_title()); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=psm-add-resource'); ?>" class="page-title-action"><?php _e('Add New', 'psm-resource-manager'); ?></a>
    
    <?php if ($total_resources > 0): ?>
    <form method="post">
        <?php wp_nonce_field('bulk_action_resources', 'bulk_nonce'); ?>
        
        <div class="tablenav top">
            <div class="alignleft actions bulkactions">
                <select name="action">
                    <option value="-1"><?php _e('Bulk Actions', 'psm-resource-manager'); ?></option>
                    <option value="delete_selected"><?php _e('Delete', 'psm-resource-manager'); ?></option>
                </select>
                <input type="submit" class="button action" value="<?php _e('Apply', 'psm-resource-manager'); ?>">
            </div>
            
            <div class="tablenav-pages">
                <span class="displaying-num"><?php printf(_n('%d item', '%d items', $total_resources, 'psm-resource-manager'), $total_resources); ?></span>
                <?php if ($total_pages > 1): ?>
                <span class="pagination-links">
                    <?php
                    echo paginate_links(array(
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;',
                        'current' => $current_page,
                        'total' => $total_pages,
                    ));
                    ?>
                </span>
                <?php endif; ?>
            </div>
        </div>
        
        <table class="wp-list-table widefat fixed striped posts">
            <thead>
                <tr>
                    <td class="manage-column column-cb check-column">
                        <input type="checkbox" id="cb-select-all-1">
                    </td>
                    <th scope="col" class="manage-column column-title"><?php _e('Title', 'psm-resource-manager'); ?></th>
                    <th scope="col" class="manage-column"><?php _e('Type', 'psm-resource-manager'); ?></th>
                    <th scope="col" class="manage-column"><?php _e('URL/File', 'psm-resource-manager'); ?></th>
                    <th scope="col" class="manage-column"><?php _e('Status', 'psm-resource-manager'); ?></th>
                    <th scope="col" class="manage-column column-date"><?php _e('Date', 'psm-resource-manager'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resources as $resource): ?>
                <tr>
                    <th scope="row" class="check-column">
                        <input type="checkbox" name="resource_ids[]" value="<?php echo esc_attr($resource->id); ?>">
                    </th>
                    <td class="column-title">
                        <strong>
                            <a href="<?php echo admin_url('post.php?post=' . $resource->post_id . '&action=edit'); ?>">
                                <?php echo esc_html($resource->post_title ?: __('(no title)', 'psm-resource-manager')); ?>
                            </a>
                        </strong>
                        <div class="row-actions">
                            <span class="edit">
                                <a href="<?php echo admin_url('post.php?post=' . $resource->post_id . '&action=edit'); ?>">
                                    <?php _e('Edit', 'psm-resource-manager'); ?>
                                </a> |
                            </span>
                            <span class="view">
                                <a href="<?php echo get_permalink($resource->post_id); ?>" target="_blank">
                                    <?php _e('View', 'psm-resource-manager'); ?>
                                </a> |
                            </span>
                            <span class="trash">
                                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=psm-resources&action=delete&id=' . $resource->id), 'delete_resource_' . $resource->id); ?>" 
                                   onclick="return confirm('<?php _e('Are you sure you want to delete this resource?', 'psm-resource-manager'); ?>');">
                                    <?php _e('Delete', 'psm-resource-manager'); ?>
                                </a>
                            </span>
                        </div>
                    </td>
                    <td>
                        <span class="resource-type resource-type-<?php echo esc_attr($resource->resource_type); ?>">
                            <?php echo esc_html(ucfirst(str_replace('-', ' ', $resource->resource_type))); ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($resource->url): ?>
                            <a href="<?php echo esc_url($resource->url); ?>" target="_blank" title="<?php echo esc_attr($resource->url); ?>">
                                <?php echo esc_html(wp_trim_words($resource->url, 5, '...')); ?>
                            </a>
                        <?php elseif ($resource->image_id): ?>
                            <span class="dashicons dashicons-format-image"></span> <?php _e('Image attached', 'psm-resource-manager'); ?>
                        <?php else: ?>
                            <span class="description"><?php _e('No file/URL', 'psm-resource-manager'); ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="status-<?php echo esc_attr($resource->post_status); ?>">
                            <?php echo esc_html(ucfirst($resource->post_status)); ?>
                        </span>
                    </td>
                    <td>
                        <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($resource->post_date))); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="tablenav bottom">
            <div class="alignleft actions bulkactions">
                <select name="action">
                    <option value="-1"><?php _e('Bulk Actions', 'psm-resource-manager'); ?></option>
                    <option value="delete_selected"><?php _e('Delete', 'psm-resource-manager'); ?></option>
                </select>
                <input type="submit" class="button action" value="<?php _e('Apply', 'psm-resource-manager'); ?>">
            </div>
        </div>
    </form>
    
    <?php else: ?>
    <div class="no-resources">
        <p><?php _e('No resources found.', 'psm-resource-manager'); ?> 
           <a href="<?php echo admin_url('admin.php?page=psm-add-resource'); ?>">
               <?php _e('Create your first resource', 'psm-resource-manager'); ?>
           </a>
        </p>
    </div>
    <?php endif; ?>
</div>

<style>
.resource-type {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 500;
    text-transform: uppercase;
}
.resource-type-pdf { background: #e3f2fd; color: #1565c0; }
.resource-type-video { background: #f3e5f5; color: #7b1fa2; }
.resource-type-podcast { background: #e8f5e8; color: #2e7d32; }
.resource-type-article { background: #fff3e0; color: #ef6c00; }
.resource-type-link { background: #fce4ec; color: #c2185b; }

.status-publish { color: #4caf50; }
.status-draft { color: #ff9800; }
.status-private { color: #2196f3; }

.no-resources {
    text-align: center;
    padding: 50px 20px;
    color: #666;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Handle select all checkbox
    $('#cb-select-all-1').on('change', function() {
        $('input[name="resource_ids[]"]').prop('checked', $(this).prop('checked'));
    });
});
</script>