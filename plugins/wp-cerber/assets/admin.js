jQuery(document).ready(function ($) {

    /* Load data with AJAX */

    if ($(".crb-table").length) {
        cerberLoadData('hostname');
        cerberLoadData('country');
    }

    if ($("table.users").length) {
        //cerberLoadData('hostname');
        cerberLoadData('country');
    }

    function cerberLoadData(slug) {
        var ip_list = $(".crb-no-" + slug).map(
            function () {
                return $(this).data('ip-id');
            }
        );
        if (ip_list.length !== 0) {
            $.post(ajaxurl, {
                action: 'cerber_ajax',
                crb_ajax_slug: slug,
                crb_ajax_list: ip_list.toArray(),
                ajax_nonce: crb_ajax_nonce
            }, cerberSetData);
        }
    }

    function cerberSetData(server_response) {
        var server_data = $.parseJSON(server_response);
        if (!server_data['data']) {
            console.log('No data loaded from server!');
            return;
        }
        var data = server_data['data'];
        var slug = server_data['slug'];
        $(".crb-no-" + slug).each(function (index) {
            if (data[$(this).data('ip-id')]) {
                $(this).replaceWith(data[$(this).data('ip-id')]);
            }
        });
    }

    // ACL management

    $(".acl-table .delete_entry").click(function () {
        /* if (!confirm('<?php _e('Are you sure?','wp-cerber') ?>')) return; */
        $.post(ajaxurl, {
                action: 'cerber_ajax',
                acl_delete: $(this).data('ip'),
                ajax_nonce: crb_ajax_nonce
            },
            onDeleteSuccess
        );
        /*$(this).parent().parent().fadeOut(500);*/
        /* $(this).closest("tr").FadeOut(500); */
    });
    function onDeleteSuccess(server_data) {
        var cerber_response = $.parseJSON(server_data);
        $('.delete_entry[data-ip="' + cerber_response['deleted_ip'] + '"]').parent().parent().fadeOut(300);
    }

    //

    /*
     $('#add-acl-black').submit(function( event ) {
     $(this).find('[name="add_acl_B"]').val($(this).find("button:focus").val());
     });
     */

    $(".cerber-dismiss").click(function () {
        $(this).closest('.cerber-msg').fadeOut(500);

        $.get(ajaxurl, {
                action: 'cerber_ajax',
                dismiss_info: 1,
                button_id: $(this).attr('id'),
            }
        );
    });

    $(".diag-text").on("keypress", function(e) {
        e.preventDefault();
    });

});
