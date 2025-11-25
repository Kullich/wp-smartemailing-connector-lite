(function($){
    function showMessage($form, type, text){
        var $m = $form.find('.secl-message');
        $m.text(text).attr('data-type', type);
    }

    $(document).on('submit', '.secl-form', function(e){
        e.preventDefault();
        var $form = $(this);
        var data = $form.serialize();

        $form.addClass('is-loading');
        showMessage($form, 'info', '');

        $.post(SECL.ajax_url, data)
            .done(function(resp){
                if (resp && resp.success) {
                    showMessage($form, 'success', resp.data && resp.data.message ? resp.data.message : 'OK');
                    $form.find('input[type=email]').val('');
                } else {
                    var msg = (resp && resp.data && resp.data.message) ? resp.data.message : 'Error';
                    showMessage($form, 'error', msg);
                }
            })
            .fail(function(xhr){
                var msg = 'Error';
                try { var d = JSON.parse(xhr.responseText); if (d && d.data && d.data.message) msg = d.data.message; } catch(e){}
                showMessage($form, 'error', msg);
            })
            .always(function(){ $form.removeClass('is-loading'); });
    });

    // Popup
    $(function(){
        if (!SECL.popup || !SECL.popup.enabled) return;
        var key = 'secl_popup_last';
        var freqHours = parseInt(SECL.popup.freq || 24, 10);
        var last = parseInt(localStorage.getItem(key) || '0', 10);
        var now = Date.now();
        if (last && (now - last) < freqHours * 3600 * 1000) return;

        setTimeout(function(){
            var $p = $('#secl-popup');
            if (!$p.length) return;
            $p.removeAttr('hidden');
            $p.on('click', function(e){ if (e.target === this) $p.attr('hidden', true); });
            $p.find('.secl-close').on('click', function(){ $p.attr('hidden', true); });
            localStorage.setItem(key, String(Date.now()));
        }, parseInt(SECL.popup.delay || 0, 10));
    });

})(jQuery);
