<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Handle form submission for adding new resource type
if (isset($_POST['submit']) && $_POST['action'] === 'add' && wp_verify_nonce($_POST['resource_type_nonce'], 'add_resource_type')) {
    $term_name = sanitize_text_field($_POST['term_name']);
    $term_slug = sanitize_title($_POST['term_slug']);
    $term_description = sanitize_textarea_field($_POST['term_description']);
    
    $result = wp_insert_term($term_name, 'psm_resource_type', array(
        'slug' => $term_slug,
        'description' => $term_description
    ));
    
    if (!is_wp_error($result)) {
        echo '<div class="notice notice-success is-dismissible"><p>' . __('Resource type added successfully!', 'psm-resource-manager') . '</p></div>';
    } else {
        echo '<div class="notice notice-error is-dismissible"><p>' . $result->get_error_message() . '</p></div>';
    }
}

// Handle deletion
if (isset($_GET['action'], $_GET['tag_ID'], $_GET['_wpnonce']) && $_GET['action'] === 'delete' && wp_verify_nonce($_GET['_wpnonce'], 'delete-tag_' . $_GET['tag_ID'])) {
    $term_id = intval($_GET['tag_ID']);
    $result = wp_delete_term($term_id, 'psm_resource_type');
    
    if (!is_wp_error($result) && $result) {
        echo '<div class="notice notice-success is-dismissible"><p>' . __('Resource type deleted successfully!', 'psm-resource-manager') . '</p></div>';
    } else {
        echo '<div class="notice notice-error is-dismissible"><p>' . __('Error deleting resource type.', 'psm-resource-manager') . '</p></div>';
    }
}

// Get all resource types
$resource_types = get_terms(array(
    'taxonomy' => 'psm_resource_type',
    'hide_empty' => false,
    'orderby' => 'name',
    'order' => 'ASC'
));
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div id="col-container" class="wp-clearfix">
        <div id="col-left">
            <div class="col-wrap">
                <div class="form-wrap">
                    <h2><?php _e('Add New Resource Type', 'psm-resource-manager'); ?></h2>
                    
                    <form method="post" class="validate">
                        <?php wp_nonce_field('add_resource_type', 'resource_type_nonce'); ?>
                        <input type="hidden" name="action" value="add">
                        
                        <div class="form-field form-required term-name-wrap">
                            <label for="term_name"><?php _e('Name', 'psm-resource-manager'); ?></label>
                            <input type="text" id="term_name" name="term_name" aria-required="true" required>
                            <p><?php _e('The name is how it appears on your site.', 'psm-resource-manager'); ?></p>
                        </div>
                        
                        <div class="form-field term-slug-wrap">
                            <label for="term_slug"><?php _e('Slug', 'psm-resource-manager'); ?></label>
                            <input type="text" id="term_slug" name="term_slug">
                            <p><?php _e('The "slug" is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.', 'psm-resource-manager'); ?></p>
                        </div>
                        
                        <div class="form-field term-description-wrap">
                            <label for="term_description"><?php _e('Description', 'psm-resource-manager'); ?></label>
                            <textarea id="term_description" name="term_description" rows="5" cols="40"></textarea>
                            <p><?php _e('The description is not prominent by default; however, some themes may show it.', 'psm-resource-manager'); ?></p>
                        </div>
                        
                        <?php submit_button(__('Add New Resource Type', 'psm-resource-manager')); ?>
                    </form>
                </div>
            </div>
        </div>
        
        <div id="col-right">
            <div class="col-wrap">
                <div class="form-wrap">
                    <h2><?php _e('Resource Types', 'psm-resource-manager'); ?></h2>
                    
                    <?php if (!empty($resource_types)): ?>
                    <table class="wp-list-table widefat fixed striped tags">
                        <thead>
                            <tr>
                                <th scope="col" class="manage-column column-name column-primary"><?php _e('Name', 'psm-resource-manager'); ?></th>
                                <th scope="col" class="manage-column column-description"><?php _e('Description', 'psm-resource-manager'); ?></th>
                                <th scope="col" class="manage-column column-slug"><?php _e('Slug', 'psm-resource-manager'); ?></th>
                                <th scope="col" class="manage-column column-posts num"><?php _e('Count', 'psm-resource-manager'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resource_types as $type): ?>
                            <tr>
                                <td class="name column-name column-primary">
                                    <strong>
                                        <a class="row-title" href="<?php echo admin_url('edit-tags.php?action=edit&taxonomy=psm_resource_type&tag_ID=' . $type->term_id); ?>">
                                            <?php echo esc_html($type->name); ?>
                                        </a>
                                    </strong>
                                    <div class="row-actions">
                                        <span class="edit">
                                            <a href="<?php echo admin_url('edit-tags.php?action=edit&taxonomy=psm_resource_type&tag_ID=' . $type->term_id); ?>">
                                                <?php _e('Edit', 'psm-resource-manager'); ?>
                                            </a> |
                                        </span>
                                        <span class="delete">
                                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=psm-resource-types&action=delete&tag_ID=' . $type->term_id), 'delete-tag_' . $type->term_id); ?>" 
                                               onclick="return confirm('<?php _e('Are you sure you want to delete this resource type?', 'psm-resource-manager'); ?>');">
                                                <?php _e('Delete', 'psm-resource-manager'); ?>
                                            </a>
                                        </span>
                                    </div>
                                </td>
                                <td class="description column-description">
                                    <span><?php echo esc_html($type->description); ?></span>
                                </td>
                                <td class="slug column-slug">
                                    <?php echo esc_html($type->slug); ?>
                                </td>
                                <td class="posts column-posts num">
                                    <?php if ($type->count > 0): ?>
                                        <a href="<?php echo admin_url('edit.php?post_type=psm_resource&psm_resource_type=' . $type->slug); ?>">
                                            <?php echo esc_html($type->count); ?>
                                        </a>
                                    <?php else: ?>
                                        <?php echo esc_html($type->count); ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <?php else: ?>
                    <div class="no-items">
                        <p><?php _e('No resource types found.', 'psm-resource-manager'); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
#col-container {
    display: flex;
    gap: 20px;
}
#col-left {
    flex: 0 0 300px;
}
#col-right {
    flex: 1;
}
.form-wrap {
    background: #fff;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
    padding: 15px;
}
.form-field {
    margin-bottom: 15px;
}
.form-field label {
    display: block;
    font-weight: 600;
    margin-bottom: 5px;
}
.form-field input,
.form-field textarea {
    width: 100%;
}
.form-field p {
    color: #666;
    font-style: italic;
    font-size: 13px;
    margin-top: 5px;
}
.no-items {
    text-align: center;
    padding: 20px;
    color: #666;
}

@media (max-width: 782px) {
    #col-container {
        flex-direction: column;
    }
    #col-left {
        flex: none;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Auto-generate slug from name
    $('#term_name').on('input', function() {
        var name = $(this).val();
        var slug = name.toLowerCase()
                      .replace(/[^a-z0-9]+/g, '-')
                      .replace(/^-+|-+$/g, '');
        $('#term_slug').val(slug);
    });
});
</script>