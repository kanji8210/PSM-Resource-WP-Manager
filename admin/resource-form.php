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
