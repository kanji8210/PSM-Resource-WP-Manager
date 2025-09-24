<?php
// Listing page for all resources with quick edit (squelette)
echo '<div class="wrap"><h1>All Resources</h1>';
// Filtres et tri
// Filters for new taxonomies
$selected_county = isset($_GET['psm_filter_county']) ? sanitize_text_field($_GET['psm_filter_county']) : '';
$selected_type = isset($_GET['psm_filter_type']) ? sanitize_text_field($_GET['psm_filter_type']) : '';
$selected_content_type = isset($_GET['psm_filter_content_type']) ? sanitize_text_field($_GET['psm_filter_content_type']) : '';
$selected_sector = isset($_GET['psm_filter_sector']) ? sanitize_text_field($_GET['psm_filter_sector']) : '';
$selected_order = isset($_GET['psm_order']) && strtolower($_GET['psm_order']) === 'asc' ? 'ASC' : 'DESC';
$all_counties = get_terms([ 'taxonomy' => 'county', 'hide_empty' => false ]);
$all_types = get_terms([ 'taxonomy' => 'type', 'hide_empty' => false ]);
$all_cts = get_terms([ 'taxonomy' => 'content_type', 'hide_empty' => false ]);
$all_sectors = get_terms([ 'taxonomy' => 'sector', 'hide_empty' => false ]);
echo '<form method="get" style="margin-bottom:20px;">';
echo '<input type="hidden" name="page" value="psm_resources">';
echo 'County: <select name="psm_filter_county"><option value="">All</option>';
foreach ($all_counties as $county) {
	$sel = ($selected_county == $county->name) ? 'selected' : '';
	echo '<option value="' . esc_attr($county->name) . '" ' . $sel . '>' . esc_html($county->name) . '</option>';
}
echo '</select> ';
echo 'Type: <select name="psm_filter_type"><option value="">All</option>';
foreach ($all_types as $type) {
	$sel = ($selected_type == $type->name) ? 'selected' : '';
	echo '<option value="' . esc_attr($type->name) . '" ' . $sel . '>' . esc_html($type->name) . '</option>';
}
echo '</select> ';
echo 'Content Type: <select name="psm_filter_content_type"><option value="">All</option>';
foreach ($all_cts as $ct) {
	$sel = ($selected_content_type == $ct->name) ? 'selected' : '';
	echo '<option value="' . esc_attr($ct->name) . '" ' . $sel . '>' . esc_html($ct->name) . '</option>';
}
echo '</select> ';
echo 'Sector: <select name="psm_filter_sector"><option value="">All</option>';
foreach ($all_sectors as $sector) {
	$sel = ($selected_sector == $sector->name) ? 'selected' : '';
	echo '<option value="' . esc_attr($sector->name) . '" ' . $sel . '>' . esc_html($sector->name) . '</option>';
}
echo '</select> ';
echo 'Sort by date: <select name="psm_order">';
echo '<option value="DESC"' . ($selected_order === 'DESC' ? ' selected' : '') . '>Newest first</option>';
echo '<option value="ASC"' . ($selected_order === 'ASC' ? ' selected' : '') . '>Oldest first</option>';
echo '</select> ';
echo '<button type="submit" class="button">Filter</button>';
echo '</form>';
echo '<div id="psm-resources-list">';
// Récupérer les ressources CPT
$query_args = [
	'post_type' => 'resource',
	'posts_per_page' => 20,
	'post_status' => 'any',
	'orderby' => 'date',
	'order' => $selected_order,
];
$tax_query = [];
if ($selected_county) $tax_query[] = [ 'taxonomy' => 'county', 'field' => 'name', 'terms' => $selected_county ];
if ($selected_type) $tax_query[] = [ 'taxonomy' => 'type', 'field' => 'name', 'terms' => $selected_type ];
if ($selected_content_type) $tax_query[] = [ 'taxonomy' => 'content_type', 'field' => 'name', 'terms' => $selected_content_type ];
if ($selected_sector) $tax_query[] = [ 'taxonomy' => 'sector', 'field' => 'name', 'terms' => $selected_sector ];
if (!empty($tax_query)) $query_args['tax_query'] = $tax_query;
$resources = get_posts($query_args);
if ($resources) {
	echo '<table class="wp-list-table widefat fixed striped">';
	echo '<thead><tr><th>Title</th><th>County</th><th>Type</th><th>Content Type</th><th>Sector</th><th>Date</th><th>Actions</th></tr></thead><tbody>';
	foreach ($resources as $res) {
		$county = get_the_terms($res->ID, 'county');
		$county_label = $county && !is_wp_error($county) ? esc_html($county[0]->name) : '-';
		$type = get_the_terms($res->ID, 'type');
		$type_label = $type && !is_wp_error($type) ? esc_html($type[0]->name) : '-';
		$ct = get_the_terms($res->ID, 'content_type');
		$ct_label = $ct && !is_wp_error($ct) ? esc_html($ct[0]->name) : '-';
		$sector = get_the_terms($res->ID, 'sector');
		$sector_label = $sector && !is_wp_error($sector) ? esc_html($sector[0]->name) : '-';
		$view_link = get_permalink($res->ID);
		$edit_link = admin_url('admin.php?page=psm_add_resource&resource_id=' . $res->ID);
		$archive_link = get_post_type_archive_link('resource');
		$status = get_post_status($res->ID);
		echo '<tr>';
		echo '<td>' . esc_html($res->post_title) . '</td>';
		echo '<td>' . $county_label . '</td>';
		echo '<td>' . $type_label . '</td>';
		echo '<td>' . $ct_label . '</td>';
		echo '<td>' . $sector_label . '</td>';
		echo '<td>' . esc_html(get_the_date('', $res->ID)) . '</td>';
		echo '<td>';
		// Quick Edit (inline, county/type/content_type/sector)
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
		// County
		$all_counties = get_terms([ 'taxonomy'=>'county', 'hide_empty'=>false ]);
		$edit_county = wp_get_object_terms($res->ID, 'county', ['fields'=>'names']);
		echo 'County: <select name="psm_quick_county[]" multiple size="2">';
		foreach ($all_counties as $county) {
			$selected = in_array($county->name, $edit_county) ? 'selected' : '';
			echo '<option value="' . esc_attr($county->name) . '" ' . $selected . '>' . esc_html($county->name) . '</option>';
		}
		echo '</select> ';
		// Type
		$all_types = get_terms([ 'taxonomy'=>'type', 'hide_empty'=>false ]);
		$edit_type = wp_get_object_terms($res->ID, 'type', ['fields'=>'names']);
		echo 'Type: <select name="psm_quick_type[]" multiple size="2">';
		foreach ($all_types as $type) {
			$selected = in_array($type->name, $edit_type) ? 'selected' : '';
			echo '<option value="' . esc_attr($type->name) . '" ' . $selected . '>' . esc_html($type->name) . '</option>';
		}
		echo '</select> ';
		// Content Type
		$all_cts = get_terms([ 'taxonomy'=>'content_type', 'hide_empty'=>false ]);
		$edit_ct = wp_get_object_terms($res->ID, 'content_type', ['fields'=>'names']);
		echo 'Content Type: <select name="psm_quick_content_type" size="1">';
		echo '<option value="">-</option>';
		foreach ($all_cts as $ct) {
			$selected = (!empty($edit_ct) && $edit_ct[0] === $ct->name) ? 'selected' : '';
			echo '<option value="' . esc_attr($ct->name) . '" ' . $selected . '>' . esc_html($ct->name) . '</option>';
		}
		echo '</select> ';
		// Sector
		$all_sectors = get_terms([ 'taxonomy'=>'sector', 'hide_empty'=>false ]);
		$edit_sector = wp_get_object_terms($res->ID, 'sector', ['fields'=>'names']);
		echo 'Sector: <select name="psm_quick_sector[]" multiple size="2">';
		foreach ($all_sectors as $sector) {
			$selected = in_array($sector->name, $edit_sector) ? 'selected' : '';
			echo '<option value="' . esc_attr($sector->name) . '" ' . $selected . '>' . esc_html($sector->name) . '</option>';
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
		$counties = isset($_POST['psm_quick_county']) ? array_map('sanitize_text_field', (array)$_POST['psm_quick_county']) : [];
		$types = isset($_POST['psm_quick_type']) ? array_map('sanitize_text_field', (array)$_POST['psm_quick_type']) : [];
		$content_type = isset($_POST['psm_quick_content_type']) ? sanitize_text_field($_POST['psm_quick_content_type']) : '';
		$sectors = isset($_POST['psm_quick_sector']) ? array_map('sanitize_text_field', (array)$_POST['psm_quick_sector']) : [];
		// Gérer archive comme statut personnalisé (sinon draft/publish)
		if ($new_status === 'archive') {
			update_post_meta($rid, '_psm_archived', 1);
			wp_update_post([ 'ID' => $rid, 'post_status' => 'draft' ]);
		} else {
			delete_post_meta($rid, '_psm_archived');
			wp_update_post([ 'ID' => $rid, 'post_status' => $new_status ]);
		}
		if (!empty($counties)) wp_set_object_terms($rid, $counties, 'county');
		if (!empty($types)) wp_set_object_terms($rid, $types, 'type');
		if (!empty($content_type)) wp_set_object_terms($rid, $content_type, 'content_type');
		if (!empty($sectors)) wp_set_object_terms($rid, $sectors, 'sector');
		echo '<div class="notice notice-success is-dismissible"><p>Resource updated (Quick Edit)!</p></div>';
	}
} else {
	echo '<p>No resources found.</p>';
}
echo '</div></div>';
