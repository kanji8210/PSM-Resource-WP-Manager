<?php
// Template PDF : Affiche description et lien de téléchargement
global $post;
$desc = get_the_content();
$url = get_post_meta($post->ID, 'psm_resource_url', true);
echo '<div class="psm-resource-pdf">';
// Template PDF : Affiche description, embed PDF lisible, bouton download et plein écran
if (!$url) return;
echo '<div class="psm-resource-pdf">';
echo '<div class="desc">' . esc_html($desc) . '</div>';
// Boutons
echo '<div style="margin-bottom:10px; margin-top:20px;">';
echo '<a href="' . esc_url($url) . '" class="button" download title="Download"><span style="font-size:18px;">&#128190;</span> Download</a> ';
echo '<a href="' . esc_url($url) . '" class="button" target="_blank" title="Open in full screen">Full screen</a>';
echo '</div>';
// Embed PDF
echo '<div style="border:1px solid #ccc; border-radius:6px; overflow:hidden;">';
echo '<iframe src="' . esc_url($url) . '#toolbar=1&navpanes=0&scrollbar=1" width="100%" height="600px" style="min-height:400px;" allowfullscreen></iframe>';
echo '</div>';
echo '</div>';
