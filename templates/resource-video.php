<?php
// Template Vidéo : Embed player selon la plateforme
global $post;
$type = get_post_meta($post->ID, 'psm_resource_platform', true);
$url = get_post_meta($post->ID, 'psm_resource_url', true);
echo '<div class="psm-resource-video">';
if ($type === 'YouTube') {
    echo '<iframe width="560" height="315" src="' . esc_url($url) . '" frameborder="0" allowfullscreen></iframe>';
} elseif ($type === 'Vimeo') {
    echo '<iframe src="' . esc_url($url) . '" width="640" height="360" frameborder="0" allowfullscreen></iframe>';
} else {
    echo '<video controls src="' . esc_url($url) . '"></video>';
}
echo '</div>';
