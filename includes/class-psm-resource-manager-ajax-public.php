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
        $county = isset($_POST['county']) ? sanitize_text_field($_POST['county']) : '';
        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';
        $content_type = isset($_POST['content_type']) ? sanitize_text_field($_POST['content_type']) : '';
        $sector = isset($_POST['sector']) ? sanitize_text_field($_POST['sector']) : '';
        $columns = isset($_POST['columns']) ? max(1, intval($_POST['columns'])) : 3;
        $per_page = isset($_POST['per_page']) ? max(1, intval($_POST['per_page'])) : 12;
        $tax_query = [];
        if ($county) $tax_query[] = [ 'taxonomy' => 'county', 'field' => 'name', 'terms' => $county ];
        if ($type) $tax_query[] = [ 'taxonomy' => 'type', 'field' => 'name', 'terms' => $type ];
        if ($content_type) $tax_query[] = [ 'taxonomy' => 'content_type', 'field' => 'name', 'terms' => $content_type ];
        if ($sector) $tax_query[] = [ 'taxonomy' => 'sector', 'field' => 'name', 'terms' => $sector ];
        $args = [
            'post_type' => 'resource',
            'posts_per_page' => $per_page,
            'paged' => $paged,
            'post_status' => 'publish',
        ];
        if (!empty($tax_query)) $args['tax_query'] = $tax_query;
        $q = new WP_Query($args);
        ob_start();
        echo '<div class="psm-resource-grid" style="display:grid;grid-template-columns:repeat(' . $columns . ',1fr);gap:18px;">';
        if ($q->have_posts()) {
            while ($q->have_posts()) { $q->the_post();
                $thumb = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                $title = get_the_title();
                $type = get_the_terms(get_the_ID(), 'type');
                $type_label = ($type && !is_wp_error($type)) ? esc_html($type[0]->name) : '-';
                $county = get_the_terms(get_the_ID(), 'county');
                $county_label = ($county && !is_wp_error($county)) ? esc_html($county[0]->name) : '-';
                $content_type = get_the_terms(get_the_ID(), 'content_type');
                $content_type_label = ($content_type && !is_wp_error($content_type)) ? esc_html($content_type[0]->name) : '-';
                $sector = get_the_terms(get_the_ID(), 'sector');
                $sector_label = ($sector && !is_wp_error($sector)) ? esc_html($sector[0]->name) : '-';
                echo '<div class="psm-resource-item" style="background:#fafbfc;padding:12px;border-radius:8px;box-shadow:0 1px 3px #0001;">';
                if ($thumb) echo '<div><a href="' . esc_url(get_permalink()) . '"><img src="'.esc_url($thumb).'" alt="'.esc_attr($title).'" style="width:100%;height:auto;border-radius:6px;max-width:160px;display:block;margin:0 auto 8px auto;"></a></div>';
                echo '<div class="psm-resource-title" style="font-weight:bold;font-size:1.1em;margin-bottom:4px;text-align:center;">' . esc_html($title) . '</div>';
                echo '<div class="psm-resource-meta" style="font-size:0.95em;color:#666;text-align:center;">Type: ' . $type_label . ' | County: ' . $county_label . ' | Content: ' . $content_type_label . ' | Sector: ' . $sector_label . '</div>';
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
            <select id="psm-filter-county"><option value="">All Counties</option><?php
            $counties = get_terms(['taxonomy'=>'county','hide_empty'=>false]);
            foreach($counties as $county) echo '<option value="'.esc_attr($county->name).'">'.esc_html($county->name).'</option>';
            ?></select>
            <select id="psm-filter-type"><option value="">All Types</option><?php
            $types = get_terms(['taxonomy'=>'type','hide_empty'=>false]);
            foreach($types as $type) echo '<option value="'.esc_attr($type->name).'">'.esc_html($type->name).'</option>';
            ?></select>
            <select id="psm-filter-content-type"><option value="">All Content Types</option><?php
            $cts = get_terms(['taxonomy'=>'content_type','hide_empty'=>false]);
            foreach($cts as $ct) echo '<option value="'.esc_attr($ct->name).'">'.esc_html($ct->name).'</option>';
            ?></select>
            <select id="psm-filter-sector"><option value="">All Sectors</option><?php
            $sectors = get_terms(['taxonomy'=>'sector','hide_empty'=>false]);
            foreach($sectors as $sector) echo '<option value="'.esc_attr($sector->name).'">'.esc_html($sector->name).'</option>';
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
                    county: $('#psm-filter-county').val(),
                    type: $('#psm-filter-type').val(),
                    content_type: $('#psm-filter-content-type').val(),
                    sector: $('#psm-filter-sector').val(),
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
