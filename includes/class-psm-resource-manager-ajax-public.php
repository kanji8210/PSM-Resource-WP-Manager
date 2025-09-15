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
        $columns = isset($_POST['columns']) ? max(1, intval($_POST['columns'])) : 3;
        $per_page = isset($_POST['per_page']) ? max(1, intval($_POST['per_page'])) : 12;
        $args = [
            'post_type' => 'resource',
            'posts_per_page' => $per_page,
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
        echo '<div class="psm-resource-grid" style="display:grid;grid-template-columns:repeat(' . $columns . ',1fr);gap:18px;">';
        if ($q->have_posts()) {
            while ($q->have_posts()) { $q->the_post();
                $thumb = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                $title = get_the_title();
                $type = get_the_terms(get_the_ID(), 'resource_type');
                $type_label = ($type && !is_wp_error($type)) ? esc_html($type[0]->name) : '-';
                $cats = get_the_category();
                $cat_labels = $cats ? implode(', ', array_map(function($c){ return esc_html($c->name); }, $cats)) : '-';
                echo '<div class="psm-resource-item" style="background:#fafbfc;padding:12px;border-radius:8px;box-shadow:0 1px 3px #0001;">';
                if ($thumb) echo '<div><a href="' . esc_url(get_permalink()) . '"><img src="'.esc_url($thumb).'" alt="'.esc_attr($title).'" style="width:100%;height:auto;border-radius:6px;max-width:160px;display:block;margin:0 auto 8px auto;"></a></div>';
                echo '<div class="psm-resource-title" style="font-weight:bold;font-size:1.1em;margin-bottom:4px;text-align:center;">' . esc_html($title) . '</div>';
                echo '<div class="psm-resource-meta" style="font-size:0.95em;color:#666;text-align:center;">Type: ' . $type_label . ' | Category: ' . $cat_labels . '</div>';
                echo '</div>';
            }
        } else {
            echo '<p style="grid-column:1/-1;text-align:center;">No resources found.</p>';
        }
        echo '</div>';
        wp_reset_postdata();
        wp_send_json_success([ 'html' => ob_get_clean() ]);
    }

    public static function shortcode_filterable_resources($atts) {
        $atts = shortcode_atts([
            'columns' => 3,
            'posts_per_page' => 12
        ], $atts);
        $columns = max(1, intval($atts['columns']));
        $per_page = max(1, intval($atts['posts_per_page']));
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
        <div id="psm-resources-list" data-columns="<?php echo esc_attr($columns); ?>" data-per-page="<?php echo esc_attr($per_page); ?>"></div>
        <button id="psm-load-more" style="display:none">Load More</button>
        <script>
        jQuery(function($){
            let page = 1;
            let loading = false;
            let columns = <?php echo esc_js($columns); ?>;
            let per_page = <?php echo esc_js($per_page); ?>;
            function loadResources(reset=false) {
                if(loading) return;
                loading = true;
                if(reset) {
                    $('#psm-resources-list').html('');
                    page = 1;
                }
                let data = {
                    action: 'psm_filter_resources',
                    page: page,
                    cat: $('#psm-filter-cat').val(),
                    type: $('#psm-filter-type').val(),
                    columns: columns,
                    per_page: per_page
                };
                $.post(psmResourceAjax.ajaxurl, data, function(resp){
                    if(resp.success) {
                        if(reset) $('#psm-resources-list').html(resp.data.html);
                        else $('#psm-resources-list').append(resp.data.html);
                        if(resp.data.html && resp.data.html.indexOf('No resources') === -1) {
                            $('#psm-load-more').show();
                        } else {
                            $('#psm-load-more').hide();
                        }
                    }
                    loading = false;
                });
            }
            $('#psm-filter-bar select').on('change', function(){ loadResources(true); });
            $('#psm-load-more').on('click', function(){ page++; loadResources(); });
            if($('#psm-resources-list').length) loadResources(true);
        });
        </script>
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
