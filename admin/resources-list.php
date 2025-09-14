<?php
// Listing page for all resources with quick edit (squelette)
echo '<div class="wrap"><h1>All Resources</h1>';
echo '<div id="psm-resources-list">';
// Récupérer les ressources CPT
$resources = get_posts([
	'post_type' => 'resource',
	'posts_per_page' => 20,
	'post_status' => 'any',
	'orderby' => 'date',
	'order' => 'DESC',
]);
if ($resources) {
	echo '<table class="wp-list-table widefat fixed striped">';
	echo '<thead><tr><th>Title</th><th>Type</th><th>Date</th><th>Actions</th></tr></thead><tbody>';
	foreach ($resources as $res) {
		$type = get_the_terms($res->ID, 'resource_type');
		$type_label = $type && !is_wp_error($type) ? esc_html($type[0]->name) : '-';
		$view_link = get_permalink($res->ID);
		$edit_link = admin_url('admin.php?page=psm_add_resource&resource_id=' . $res->ID);
		$archive_link = get_post_type_archive_link('resource');
		$status = get_post_status($res->ID);
		echo '<tr>';
		echo '<td>' . esc_html($res->post_title) . '</td>';
		echo '<td>' . $type_label . '</td>';
		echo '<td>' . esc_html(get_the_date('', $res->ID)) . '</td>';
		echo '<td>';
		// Quick Edit (inline, catégorie seulement)
		echo '<details><summary>Quick Edit</summary>';
		echo '<form method="post" style="margin:0;display:inline-block;">';
		wp_nonce_field('psm_quick_edit_resource_' . $res->ID, 'psm_quick_edit_nonce');
		echo '<input type="hidden" name="psm_quick_edit_id" value="' . esc_attr($res->ID) . '">';
		// Statut
		echo 'Status: <select name="psm_quick_status">';
		foreach ([ 'publish' => 'Published', 'draft' => 'Draft', 'archive' => 'Archived' ] as $val => $label) {
			$selected = ($status === $val) ? 'selected' : '';
			echo '<option value="' . esc_attr($val) . '" ' . $selected . '>' . esc_html($label) . '</option>';
		}
		echo '</select> ';
		// Catégorie
		$all_cats = get_categories([ 'hide_empty' => false ]);
		$edit_cat = wp_get_post_categories($res->ID);
		echo 'Category: <select name="psm_quick_category[]" multiple size="2">';
		foreach ($all_cats as $cat) {
			$selected = in_array($cat->term_id, $edit_cat) ? 'selected' : '';
			echo '<option value="' . esc_attr($cat->term_id) . '" ' . $selected . '>' . esc_html($cat->name) . '</option>';
		}
		echo '</select> ';
		echo '<button type="submit" class="button">Save</button>';
		echo '</form></details> ';
		// Full Edit
		echo '<a href="' . esc_url($edit_link) . '">Full Edit</a> | ';
		echo '<a href="' . esc_url($view_link) . '" target="_blank">View</a> | ';
		echo '<a href="' . esc_url($archive_link) . '" target="_blank">Archive</a>';
		echo '</td>';
		echo '</tr>';
	}
	echo '</tbody></table>';

	// Traitement Quick Edit
	if (isset($_POST['psm_quick_edit_id']) && isset($_POST['psm_quick_edit_nonce']) && wp_verify_nonce($_POST['psm_quick_edit_nonce'], 'psm_quick_edit_resource_' . intval($_POST['psm_quick_edit_id']))) {
		$rid = intval($_POST['psm_quick_edit_id']);
		$new_status = sanitize_text_field($_POST['psm_quick_status']);
		$cats = isset($_POST['psm_quick_category']) ? array_map('intval', (array)$_POST['psm_quick_category']) : [];
		// Gérer archive comme statut personnalisé (sinon draft/publish)
		if ($new_status === 'archive') {
			update_post_meta($rid, '_psm_archived', 1);
			wp_update_post([ 'ID' => $rid, 'post_status' => 'draft' ]);
		} else {
			delete_post_meta($rid, '_psm_archived');
			wp_update_post([ 'ID' => $rid, 'post_status' => $new_status ]);
		}
		// Catégorie
		if (!empty($cats)) {
			wp_set_post_categories($rid, $cats);
		}
		echo '<div class="notice notice-success is-dismissible"><p>Resource updated (Quick Edit)!</p></div>';
	}
} else {
	echo '<p>No resources found.</p>';
}
echo '</div></div>';
