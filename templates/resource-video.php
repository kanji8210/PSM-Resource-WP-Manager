<?php
// Video Template: Show title, description, and embed video

global $post;
$title = get_the_title();
$desc = get_the_content();
$platform = get_post_meta($post->ID, 'psm_resource_platform', true);
$url = get_post_meta($post->ID, 'psm_resource_url', true);

if (!$url) return;

echo '<div class="psm-resource-video">';
echo '<h2>' . esc_html($title) . '</h2>';
echo '<div class="desc">' . esc_html($desc) . '</div>';

// Embed logic
if ($platform === 'YouTube') {
    // Accept both full and short YouTube URLs
    if (preg_match('~(?:youtu.be/|youtube.com/(?:embed/|v/|watch\?v=))([\w-]{11})~', $url, $matches)) {
        $yt_id = $matches[1];
        $embed_url = 'https://www.youtube.com/embed/' . $yt_id;
        echo '<iframe width="560" height="315" src="' . esc_url($embed_url) . '" frameborder="0" allowfullscreen></iframe>';
    } else {
        echo '<a href="' . esc_url($url) . '" target="_blank">Watch on YouTube</a>';
    }
} elseif ($platform === 'Vimeo') {
    // Accept vimeo.com/12345678 or player.vimeo.com/video/12345678
    if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $matches)) {
        $vimeo_id = $matches[1];
        $embed_url = 'https://player.vimeo.com/video/' . $vimeo_id;
        echo '<iframe src="' . esc_url($embed_url) . '" width="640" height="360" frameborder="0" allowfullscreen></iframe>';
    } else {
        echo '<a href="' . esc_url($url) . '" target="_blank">Watch on Vimeo</a>';
    }
} else {
    echo '<video controls src="' . esc_url($url) . '" style="max-width:100%;height:auto;"></video>';
}
echo '</div>';
