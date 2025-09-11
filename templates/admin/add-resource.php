<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get resource types
$resource_types = get_terms(array(
    'taxonomy' => 'psm_resource_type',
    'hide_empty' => false,
));
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <form method="post" enctype="multipart/form-data" id="psm-resource-form">
        <?php wp_nonce_field('psm_add_resource', 'psm_nonce'); ?>
        
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="resource_title"><?php _e('Title', 'psm-resource-manager'); ?> <span class="required">*</span></label>
                    </th>
                    <td>
                        <input type="text" id="resource_title" name="resource_title" class="regular-text" required>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="resource_description"><?php _e('Description', 'psm-resource-manager'); ?></label>
                    </th>
                    <td>
                        <?php
                        wp_editor('', 'resource_description', array(
                            'textarea_name' => 'resource_description',
                            'media_buttons' => true,
                            'textarea_rows' => 5,
                            'teeny' => false,
                            'tinymce' => true
                        ));
                        ?>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="resource_type"><?php _e('Resource Type', 'psm-resource-manager'); ?> <span class="required">*</span></label>
                    </th>
                    <td>
                        <select id="resource_type" name="resource_type" required>
                            <option value=""><?php _e('Select Resource Type', 'psm-resource-manager'); ?></option>
                            <?php foreach ($resource_types as $type): ?>
                                <option value="<?php echo esc_attr($type->slug); ?>"><?php echo esc_html($type->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                
                <!-- Dynamic fields based on resource type -->
                <tr id="pdf-fields" class="type-specific-fields" style="display: none;">
                    <th scope="row">
                        <label for="resource_image"><?php _e('Upload Image/Thumbnail', 'psm-resource-manager'); ?></label>
                    </th>
                    <td>
                        <input type="hidden" id="resource_image_id" name="resource_image_id">
                        <div id="resource_image_preview"></div>
                        <button type="button" class="button" id="upload_image_button"><?php _e('Upload Image', 'psm-resource-manager'); ?></button>
                        <button type="button" class="button" id="remove_image_button" style="display: none;"><?php _e('Remove Image', 'psm-resource-manager'); ?></button>
                        <p class="description"><?php _e('Upload an image or thumbnail for the PDF resource.', 'psm-resource-manager'); ?></p>
                    </td>
                </tr>
                
                <tr id="video-podcast-fields" class="type-specific-fields" style="display: none;">
                    <th scope="row">
                        <label for="resource_url"><?php _e('URL', 'psm-resource-manager'); ?></label>
                    </th>
                    <td>
                        <input type="url" id="resource_url" name="resource_url" class="regular-text" placeholder="https://example.com/video-or-podcast">
                        <p class="description"><?php _e('Enter the URL for the video or podcast.', 'psm-resource-manager'); ?></p>
                    </td>
                </tr>
                
                <tr id="hosting-options-fields" class="type-specific-fields" style="display: none;">
                    <th scope="row">
                        <label for="hosting_option"><?php _e('Hosting Option', 'psm-resource-manager'); ?></label>
                    </th>
                    <td>
                        <select id="hosting_option" name="hosting_option">
                            <option value=""><?php _e('Select Hosting Platform', 'psm-resource-manager'); ?></option>
                            <option value="youtube"><?php _e('YouTube', 'psm-resource-manager'); ?></option>
                            <option value="vimeo"><?php _e('Vimeo', 'psm-resource-manager'); ?></option>
                            <option value="spotify"><?php _e('Spotify', 'psm-resource-manager'); ?></option>
                            <option value="soundcloud"><?php _e('SoundCloud', 'psm-resource-manager'); ?></option>
                            <option value="apple-podcasts"><?php _e('Apple Podcasts', 'psm-resource-manager'); ?></option>
                            <option value="google-podcasts"><?php _e('Google Podcasts', 'psm-resource-manager'); ?></option>
                            <option value="other"><?php _e('Other', 'psm-resource-manager'); ?></option>
                        </select>
                        <p class="description"><?php _e('Select the hosting platform for your video or podcast.', 'psm-resource-manager'); ?></p>
                    </td>
                </tr>
                
                <tr id="link-fields" class="type-specific-fields" style="display: none;">
                    <th scope="row">
                        <label for="external_url"><?php _e('External URL', 'psm-resource-manager'); ?></label>
                    </th>
                    <td>
                        <input type="url" id="external_url" name="external_url" class="regular-text" placeholder="https://example.com">
                        <p class="description"><?php _e('Enter the external URL for this link resource.', 'psm-resource-manager'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="resource_excerpt"><?php _e('Short Description', 'psm-resource-manager'); ?></label>
                    </th>
                    <td>
                        <textarea id="resource_excerpt" name="resource_excerpt" rows="3" class="large-text"></textarea>
                        <p class="description"><?php _e('Enter a short description or excerpt for this resource.', 'psm-resource-manager'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="resource_tags"><?php _e('Tags', 'psm-resource-manager'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="resource_tags" name="resource_tags" class="regular-text">
                        <p class="description"><?php _e('Enter tags separated by commas.', 'psm-resource-manager'); ?></p>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <?php submit_button(__('Save Resource', 'psm-resource-manager')); ?>
    </form>
</div>

<style>
.required {
    color: #d63638;
}
.type-specific-fields {
    transition: all 0.3s ease;
}
#resource_image_preview img {
    max-width: 150px;
    height: auto;
    margin-bottom: 10px;
    display: block;
}
</style>