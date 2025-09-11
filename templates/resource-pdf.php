<?php
// Template PDF : Affiche description et lien de téléchargement
global $post;
$desc = get_the_content();
$url = get_post_meta($post->ID, 'psm_resource_url', true);
echo '<div class="psm-resource-pdf">';
echo '<div class="desc">' . esc_html($desc) . '</div>';
echo '<a href="' . esc_url($url) . '" class="button" download>Télécharger le PDF</a>';
echo '</div>';
