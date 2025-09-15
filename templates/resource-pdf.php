<?php
// PDF Template: Show title, description, and embed PDF

global $post;
$title = get_the_title();
$desc = get_the_content();
$url = get_post_meta($post->ID, 'psm_resource_url', true);

if (!$url) return;

echo '<div class="psm-resource-pdf">';
echo '<h2>' . esc_html($title) . '</h2>';
echo '<div class="desc">' . esc_html($desc) . '</div>';
echo '<div style="border:1px solid #ccc; border-radius:6px; overflow:hidden; margin-top:16px;">';
echo '<iframe src="' . esc_url($url) . '#toolbar=1&navpanes=0&scrollbar=1" width="100%" height="600px" style="min-height:400px;" allowfullscreen></iframe>';
echo '</div>';
echo '</div>';
