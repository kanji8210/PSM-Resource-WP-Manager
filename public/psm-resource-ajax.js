// JS pour AJAX filtrage et scroll infini côté public
jQuery(function($){
    let page = 1;
    let loading = false;
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
            type: $('#psm-filter-type').val()
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
    // Chargement initial
    if($('#psm-resources-list').length) loadResources(true);
});
