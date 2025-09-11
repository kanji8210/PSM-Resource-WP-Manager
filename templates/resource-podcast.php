<?php
// Template Podcast : Embed audio player
global $post;
$url = get_post_meta($post->ID, 'psm_resource_url', true);
echo '<div class="psm-resource-podcast">';
echo '<audio controls src="' . esc_url($url) . '"></audio>';
echo '</div>';
