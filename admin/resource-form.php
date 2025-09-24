<?php
// Formulaire dynamique pour création/édition de ressource (squelette)
// Pré-remplir si édition
$edit_id = isset($_GET['resource_id']) ? intval($_GET['resource_id']) : 0;
$edit_post = $edit_id ? get_post($edit_id) : null;
$edit_county = $edit_post ? wp_get_object_terms($edit_id, 'county', ['fields'=>'names']) : [];
$edit_type = $edit_post ? wp_get_object_terms($edit_id, 'type', ['fields'=>'names']) : [];
$edit_content_type = $edit_post ? wp_get_object_terms($edit_id, 'content_type', ['fields'=>'names']) : [];
$edit_sector = $edit_post ? wp_get_object_terms($edit_id, 'sector', ['fields'=>'names']) : [];
$edit_host = $edit_post ? get_post_meta($edit_id, 'psm_resource_platform', true) : '';
$edit_url = $edit_post ? get_post_meta($edit_id, 'psm_resource_url', true) : '';
$edit_title = $edit_post ? esc_attr($edit_post->post_title) : '';
$edit_desc = $edit_post ? esc_textarea($edit_post->post_content) : '';

echo '<div class="wrap"><h1 style="margin-bottom:24px;">' . ($edit_id ? 'Edit Resource' : 'Add Resource') . '</h1>';
echo '<form id="psm-resource-form" method="post" enctype="multipart/form-data" style="max-width:700px;background:#fff;padding:32px 32px 24px 32px;border-radius:12px;box-shadow:0 2px 12px #0002;">';
wp_nonce_field( 'psm_save_resource', 'psm_resource_nonce' );
if ($edit_id) echo '<input type="hidden" name="psm_edit_id" value="' . esc_attr($edit_id) . '">';
echo '<table class="form-table" style="width:100%;">';
// Titre
echo '<tr><th style="width:180px;"><label for="psm_title">Title</label></th><td><input type="text" name="psm_title" id="psm_title" class="regular-text" style="width:100%;font-size:1.1em;padding:8px;" required value="' . $edit_title . '"></td></tr>';
// Thumbnail
$thumb_id = $edit_post ? get_post_thumbnail_id($edit_id) : 0;
$thumb_url = $thumb_id ? wp_get_attachment_url($thumb_id) : '';
echo '<tr><th><label for="psm_thumbnail">Thumbnail</label></th><td>';
echo '<input type="file" name="psm_thumbnail" id="psm_thumbnail" accept="image/*">';
if ($thumb_url) {
	echo '<br><img src="' . esc_url($thumb_url) . '" alt="Current thumbnail" style="max-width:140px;max-height:140px;display:block;margin-top:8px;border-radius:8px;box-shadow:0 1px 4px #0001;">';
}
echo '</td></tr>';
// Description
echo '<tr><th><label for="psm_description">Description</label></th><td><textarea name="psm_description" id="psm_description" rows="5" class="large-text" style="width:100%;font-size:1.05em;padding:8px;min-height:90px;" required>' . $edit_desc . '</textarea></td></tr>';
// County taxonomy (multi)
$all_counties = get_terms([ 'taxonomy'=>'county', 'hide_empty'=>false ]);
echo '<tr><th><label for="psm_county">Country</label></th><td>';
echo '<select name="psm_county[]" id="psm_county" multiple size="6" style="width:100%;max-width:340px;">';
foreach ($all_counties as $county) {
	$selected = in_array($county->name, $edit_county) ? 'selected' : '';
	echo '<option value="' . esc_attr($county->name) . '" ' . $selected . '>' . esc_html($county->name) . '</option>';
}
echo '</select> <span style="font-size:11px">(Ctrl+click to multi-select)</span>';
echo '</td></tr>';
// Type taxonomy (multi)
$all_types = get_terms([ 'taxonomy'=>'type', 'hide_empty'=>false ]);
echo '<tr><th><label for="psm_type">Type</label></th><td>';
echo '<select name="psm_type[]" id="psm_type" multiple size="4" style="width:100%;max-width:340px;">';
foreach ($all_types as $type) {
	$selected = in_array($type->name, $edit_type) ? 'selected' : '';
	echo '<option value="' . esc_attr($type->name) . '" ' . $selected . '>' . esc_html($type->name) . '</option>';
}
echo '</select> <span style="font-size:11px">(Ctrl+click to multi-select, subtypes allowed)</span>';
echo '</td></tr>';
// Content Type taxonomy (single)
$all_cts = get_terms([ 'taxonomy'=>'content_type', 'hide_empty'=>false ]);
echo '<tr><th><label for="psm_content_type">Content Type</label></th><td>';
echo '<select name="psm_content_type" id="psm_content_type" required style="width:100%;max-width:340px;">';
echo '<option value="">Select content type</option>';
foreach ($all_cts as $ct) {
	$selected = (!empty($edit_content_type) && $edit_content_type[0] === $ct->name) ? 'selected' : '';
	echo '<option value="' . esc_attr($ct->name) . '" ' . $selected . '>' . esc_html($ct->name) . '</option>';
}
echo '</select></td></tr>';
// Sector taxonomy (multi)
$all_sectors = get_terms([ 'taxonomy'=>'sector', 'hide_empty'=>false ]);
echo '<tr><th><label for="psm_sector">Sector</label></th><td>';
echo '<select name="psm_sector[]" id="psm_sector" multiple size="3" style="width:100%;max-width:340px;">';
foreach ($all_sectors as $sector) {
	$selected = in_array($sector->name, $edit_sector) ? 'selected' : '';
	echo '<option value="' . esc_attr($sector->name) . '" ' . $selected . '>' . esc_html($sector->name) . '</option>';
}
echo '</select> <span style="font-size:11px">(Ctrl+click to multi-select)</span>';
echo '</td></tr>';
// PDF: upload
echo '<tr class="psm-type-row psm-type-pdf"><th><label for="psm_pdf">PDF File</label></th><td><input type="file" name="psm_pdf" id="psm_pdf" accept="application/pdf">';
if ($edit_type_val === 'pdf' && $edit_url) {
	echo '<br><a href="' . esc_url($edit_url) . '" target="_blank">Current PDF</a>';
}
echo '</td></tr>';
// Video/Podcast: host + url
$hosts = [ 'YouTube', 'Vimeo', 'Spotify', 'SoundCloud', 'Dailymotion', 'Autre' ];
echo '<tr class="psm-type-row psm-type-video psm-type-podcast"><th><label for="psm_host">Hosting Platform</label></th><td>';
echo '<select name="psm_host" id="psm_host">';
foreach ( $hosts as $host ) {
	$selected = ($edit_host === $host) ? 'selected' : '';
	echo '<option value="' . esc_attr($host) . '" ' . $selected . '>' . esc_html($host) . '</option>';
}
echo '</select></td></tr>';
echo '<tr class="psm-type-row psm-type-video psm-type-podcast"><th><label for="psm_url">Resource URL</label></th><td><input type="url" name="psm_url" id="psm_url" class="regular-text" value="' . esc_attr($edit_url) . '"></td></tr>';
echo '</table>';
echo '<tr><td colspan="2" style="padding-top:24px;text-align:center;"><input type="submit" class="button-primary" style="font-size:1.1em;padding:10px 32px;border-radius:6px;" value="Save Resource"></td></tr>';
echo '</form></div>';
// JS pour afficher/masquer dynamiquement les champs
echo '<script>jQuery(function($){
	function updateFields() {
		var type = $("#psm_type").val();
		$(".psm-type-row").hide();
		$(".psm-type-"+type).show();
	}
	$("#psm_type").on("change", updateFields);
	updateFields();
});</script>';
// Gestion soumission formulaire
if (isset($_POST['psm_resource_nonce']) && wp_verify_nonce($_POST['psm_resource_nonce'], 'psm_save_resource')) {
	// Upload thumbnail
	$thumb_id = 0;
	if (isset($_FILES['psm_thumbnail']) && $_FILES['psm_thumbnail']['size'] > 0) {
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		$uploaded = media_handle_upload('psm_thumbnail', 0);
		if (!is_wp_error($uploaded)) {
			$thumb_id = $uploaded;
		}
	}
	$title = sanitize_text_field($_POST['psm_title']);
	$desc = sanitize_textarea_field($_POST['psm_description']);
	$counties = isset($_POST['psm_county']) ? array_map('sanitize_text_field', (array)$_POST['psm_county']) : [];
	$types = isset($_POST['psm_type']) ? array_map('sanitize_text_field', (array)$_POST['psm_type']) : [];
	$content_type = isset($_POST['psm_content_type']) ? sanitize_text_field($_POST['psm_content_type']) : '';
	$sectors = isset($_POST['psm_sector']) ? array_map('sanitize_text_field', (array)$_POST['psm_sector']) : [];
	$host = isset($_POST['psm_host']) ? sanitize_text_field($_POST['psm_host']) : '';
	$url = isset($_POST['psm_url']) ? esc_url_raw($_POST['psm_url']) : '';
	$cats = isset($_POST['psm_category']) ? array_map('intval', (array)$_POST['psm_category']) : [];
	$pdf_url = '';
	if ($content_type === 'pdf' && isset($_FILES['psm_pdf']) && $_FILES['psm_pdf']['size'] > 0) {
		$upload_dir = wp_upload_dir();
		$psm_dir = $upload_dir['basedir'] . '/PSM_RMA/pdfs';
		if (!file_exists($psm_dir)) {
			wp_mkdir_p($psm_dir);
		}
		$file = $_FILES['psm_pdf'];
		$filename = wp_unique_filename($psm_dir, $file['name']);
		$target = $psm_dir . '/' . $filename;
		if (move_uploaded_file($file['tmp_name'], $target)) {
			$pdf_url = $upload_dir['baseurl'] . '/PSM_RMA/pdfs/' . $filename;
		}
	}
	// Créer ou mettre à jour le post
	$postarr = [
		'post_title' => $title,
		'post_content' => $desc,
		'post_type' => 'resource',
		'post_status' => 'publish',
	];
	if (isset($_POST['psm_edit_id'])) {
		$postarr['ID'] = intval($_POST['psm_edit_id']);
	}
	$post_id = isset($postarr['ID']) ? wp_update_post($postarr) : wp_insert_post($postarr);
	if ($post_id && !is_wp_error($post_id)) {
		if ($thumb_id) {
			set_post_thumbnail($post_id, $thumb_id);
		}
		if (!empty($counties)) wp_set_object_terms($post_id, $counties, 'county');
		if (!empty($types)) wp_set_object_terms($post_id, $types, 'type');
		if (!empty($content_type)) wp_set_object_terms($post_id, $content_type, 'content_type');
		if (!empty($sectors)) wp_set_object_terms($post_id, $sectors, 'sector');
		if ($content_type === 'pdf') {
			update_post_meta($post_id, 'psm_resource_url', $pdf_url);
		} else {
			update_post_meta($post_id, 'psm_resource_platform', $host);
			update_post_meta($post_id, 'psm_resource_url', $url);
		}
		// Message debug + lien
		$view_link = get_permalink($post_id);
		echo '<div class="notice notice-success is-dismissible"><p>Resource saved! <a href="' . esc_url($view_link) . '" target="_blank">View Resource</a></p></div>';
	} else {
		echo '<div class="notice notice-error is-dismissible"><p>Error saving resource.</p></div>';
	}
}
