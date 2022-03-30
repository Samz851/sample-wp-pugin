(function($){
    let zSyncBtn = $('a#ad-sync-zoho');
        zSyncBtn.live('click', SyncProduct);

    function SyncProduct(e){
        e.preventDefault();

        let data = {
            'id' : $(e.target).attr('data-id'),
            'action' : 'ad_sync_z_product'
        }
        $.post(ajaxurl, data, response => {
            if(response.success){
                $(e.target).replaceWith("<span class='dashicons dashicons-yes-alt'></span>");
            }
        })
    }
})(jQuery)