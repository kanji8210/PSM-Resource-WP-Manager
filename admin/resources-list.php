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
		echo '<tr>';
		echo '<td>' . esc_html($res->post_title) . '</td>';
		echo '<td>' . $type_label . '</td>';
		echo '<td>' . esc_html(get_the_date('', $res->ID)) . '</td>';
		echo '<td>';
		echo '<a href="' . esc_url($edit_link) . '">Quick Edit</a> | ';
		echo '<a href="' . esc_url($view_link) . '" target="_blank">View</a> | ';
		echo '<a href="' . esc_url($archive_link) . '" target="_blank">Archive</a>';
		echo '</td>';
		echo '</tr>';
	}
	echo '</tbody></table>';
} else {
	echo '<p>No resources found.</p>';
}
echo '</div></div>';
