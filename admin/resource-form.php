<?php
// Formulaire dynamique pour création/édition de ressource (squelette)
echo '<div class="wrap"><h1>Add/Edit Resource</h1>';
echo '<form id="psm-resource-form" method="post" enctype="multipart/form-data">';
wp_nonce_field( 'psm_save_resource', 'psm_resource_nonce' );
echo '<table class="form-table">';
// Titre
echo '<tr><th><label for="psm_title">Title</label></th><td><input type="text" name="psm_title" id="psm_title" class="regular-text" required></td></tr>';
// Description
echo '<tr><th><label for="psm_description">Description</label></th><td><textarea name="psm_description" id="psm_description" rows="4" class="large-text" required></textarea></td></tr>';
// Type de ressource
$types = [ 'pdf' => 'PDF', 'video' => 'Video', 'podcast' => 'Podcast' ];
echo '<tr><th><label for="psm_type">Resource Type</label></th><td>';
echo '<select name="psm_type" id="psm_type" required>';
foreach ( $types as $val => $label ) {
		echo '<option value="' . esc_attr($val) . '">' . esc_html($label) . '</option>';
}
echo '</select></td></tr>';
// PDF: upload
echo '<tr class="psm-type-row psm-type-pdf"><th><label for="psm_pdf">PDF File</label></th><td><input type="file" name="psm_pdf" id="psm_pdf" accept="application/pdf"></td></tr>';
// Video/Podcast: host + url
$hosts = [ 'YouTube', 'Vimeo', 'Spotify', 'SoundCloud', 'Dailymotion', 'Autre' ];
echo '<tr class="psm-type-row psm-type-video psm-type-podcast"><th><label for="psm_host">Hosting Platform</label></th><td>';
echo '<select name="psm_host" id="psm_host">';
foreach ( $hosts as $host ) {
		echo '<option value="' . esc_attr($host) . '">' . esc_html($host) . '</option>';
}
echo '</select></td></tr>';
echo '<tr class="psm-type-row psm-type-video psm-type-podcast"><th><label for="psm_url">Resource URL</label></th><td><input type="url" name="psm_url" id="psm_url" class="regular-text"></td></tr>';
echo '</table>';
echo '<p class="submit"><input type="submit" class="button-primary" value="Save Resource"></p>';
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
	$title = sanitize_text_field($_POST['psm_title']);
	$desc = sanitize_textarea_field($_POST['psm_description']);
	$type = sanitize_text_field($_POST['psm_type']);
	$host = isset($_POST['psm_host']) ? sanitize_text_field($_POST['psm_host']) : '';
	$url = isset($_POST['psm_url']) ? esc_url_raw($_POST['psm_url']) : '';
	$pdf_url = '';
	if ($type === 'pdf' && isset($_FILES['psm_pdf']) && $_FILES['psm_pdf']['size'] > 0) {
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
	// Créer le post
	$post_id = wp_insert_post([
		'post_title' => $title,
		'post_content' => $desc,
		'post_type' => 'resource',
		'post_status' => 'publish',
	]);
	if ($post_id && !is_wp_error($post_id)) {
		wp_set_object_terms($post_id, ucfirst($type), 'resource_type');
		if ($type === 'pdf') {
			update_post_meta($post_id, 'psm_resource_url', $pdf_url);
		} else {
			update_post_meta($post_id, 'psm_resource_platform', $host);
			update_post_meta($post_id, 'psm_resource_url', $url);
		}
		// Message debug + lien
		$view_link = get_permalink($post_id);
		echo '<div class="notice notice-success is-dismissible"><p>Resource created! <a href="' . esc_url($view_link) . '" target="_blank">View Resource</a></p></div>';
	} else {
		echo '<div class="notice notice-error is-dismissible"><p>Error creating resource.</p></div>';
	}
}
