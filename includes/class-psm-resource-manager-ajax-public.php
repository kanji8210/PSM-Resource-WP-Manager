<?php
/**
 * Classe AJAX et shortcodes pour affichage filtrable des ressources côté public
 */
class PSM_Resource_Manager_Ajax_Public {
    public static function init() {
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_scripts']);
        add_action('wp_ajax_psm_filter_resources', [__CLASS__, 'ajax_filter_resources']);
        add_action('wp_ajax_nopriv_psm_filter_resources', [__CLASS__, 'ajax_filter_resources']);
        add_shortcode('psm_filterable_resources', [__CLASS__, 'shortcode_filterable_resources']);
        add_shortcode('psm_category_filter_thumbnails', [__CLASS__, 'shortcode_category_filter_thumbnails']);
    }

    public static function enqueue_scripts() {
        wp_enqueue_script('psm-resource-ajax', PSM_RM_PLUGIN_URL . 'public/psm-resource-ajax.js', ['jquery'], PSM_RM_VERSION, true);
        wp_localize_script('psm-resource-ajax', 'psmResourceAjax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
        ]);
        wp_enqueue_style('psm-resource-public', PSM_RM_PLUGIN_URL . 'public/psm-resource-public.css', [], PSM_RM_VERSION);
    }

    public static function ajax_filter_resources() {
        $paged = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $cat = isset($_POST['cat']) ? intval($_POST['cat']) : 0;
        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';
        $args = [
            'post_type' => 'resource',
            'posts_per_page' => 8,
            'paged' => $paged,
            'post_status' => 'publish',
        ];
        if ($cat) $args['category__in'] = [$cat];
        if ($type) {
            $args['tax_query'] = [[
                'taxonomy' => 'resource_type',
                'field' => 'name',
                'terms' => $type,
            ]];
        }
        $q = new WP_Query($args);
        ob_start();
        if ($q->have_posts()) {
            while ($q->have_posts()) { $q->the_post();
                get_template_part('templates/resource', get_post_meta(get_the_ID(), 'psm_resource_platform', true));
            }
        } else {
            echo '<p>No resources found.</p>';
        }
        wp_reset_postdata();
        wp_send_json_success([ 'html' => ob_get_clean() ]);
    }

    public static function shortcode_filterable_resources($atts) {
        ob_start();
        ?>
        <div id="psm-filter-bar">
            <select id="psm-filter-type"><option value="">All Types</option><?php
            $types = get_terms(['taxonomy'=>'resource_type','hide_empty'=>false]);
            foreach($types as $type) echo '<option value="'.esc_attr($type->name).'">'.esc_html($type->name).'</option>';
            ?></select>
            <select id="psm-filter-cat"><option value="">All Categories</option><?php
            $cats = get_categories(['hide_empty'=>false]);
            foreach($cats as $cat) echo '<option value="'.esc_attr($cat->term_id).'">'.esc_html($cat->name).'</option>';
            ?></select>
        </div>
        <div id="psm-resources-list"></div>
        <button id="psm-load-more" style="display:none">Load More</button>
        <?php
        return ob_get_clean();
    }

    public static function shortcode_category_filter_thumbnails($atts) {
        $atts = shortcode_atts(['cat'=>0], $atts);
        $cat = intval($atts['cat']);
        $args = [
            'post_type' => 'resource',
            'posts_per_page' => 12,
            'post_status' => 'publish',
        ];
        if ($cat) $args['category__in'] = [$cat];
        $q = new WP_Query($args);
        ob_start();
        echo '<div class="psm-thumbnails">';
        if ($q->have_posts()) {
            while ($q->have_posts()) { $q->the_post();
                $thumb = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                echo '<div class="psm-thumb-item">';
                if ($thumb) echo '<img src="'.esc_url($thumb).'" alt="'.esc_attr(get_the_title()).'" />';
                echo '<div class="psm-thumb-title">'.esc_html(get_the_title()).'</div>';
                echo '</div>';
            }
        } else {
            echo '<p>No resources found.</p>';
        }
        echo '</div>';
        wp_reset_postdata();
        return ob_get_clean();
    }
}

PSM_Resource_Manager_Ajax_Public::init();
