var allps;

var WPDM = {


    beep: function() {
        if(WPDM.audio == undefined)
            var snd = new  Audio("data:audio/wav;base64,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qJrx4KOQjahZxWbcZgztj2c49nKmkId44S71j0c8eV9yDK6uPRzx5X18eDvjvQ6yKo9ZSS6l//8elePK/Lf//IInrOF/FvDoADYAGBMGb7FtErm5MXMlmPAJQVgWta7Zx2go+8xJ0UiCb8LHHdftWyLJE0QIAIsI+UbXu67dZMjmgDGCGl1H+vpF4NSDckSIkk7Vd+sxEhBQMRU8j/12UIRhzSaUdQ+rQU5kGeFxm+hb1oh6pWWmv3uvmReDl0UnvtapVaIzo1jZbf/pD6ElLqSX+rUmOQNpJFa/r+sa4e/pBlAABoAAAAA3CUgShLdGIxsY7AUABPRrgCABdDuQ5GC7DqPQCgbbJUAoRSUj+NIEig0YfyWUho1VBBBA//uQZB4ABZx5zfMakeAAAAmwAAAAF5F3P0w9GtAAACfAAAAAwLhMDmAYWMgVEG1U0FIGCBgXBXAtfMH10000EEEEEECUBYln03TTTdNBDZopopYvrTTdNa325mImNg3TTPV9q3pmY0xoO6bv3r00y+IDGid/9aaaZTGMuj9mpu9Mpio1dXrr5HERTZSmqU36A3CumzN/9Robv/Xx4v9ijkSRSNLQhAWumap82WRSBUqXStV/YcS+XVLnSS+WLDroqArFkMEsAS+eWmrUzrO0oEmE40RlMZ5+ODIkAyKAGUwZ3mVKmcamcJnMW26MRPgUw6j+LkhyHGVGYjSUUKNpuJUQoOIAyDvEyG8S5yfK6dhZc0Tx1KI/gviKL6qvvFs1+bWtaz58uUNnryq6kt5RzOCkPWlVqVX2a/EEBUdU1KrXLf40GoiiFXK///qpoiDXrOgqDR38JB0bw7SoL+ZB9o1RCkQjQ2CBYZKd/+VJxZRRZlqSkKiws0WFxUyCwsKiMy7hUVFhIaCrNQsKkTIsLivwKKigsj8XYlwt/WKi2N4d//uQRCSAAjURNIHpMZBGYiaQPSYyAAABLAAAAAAAACWAAAAApUF/Mg+0aohSIRobBAsMlO//Kk4soosy1JSFRYWaLC4qZBYWFRGZdwqKiwkNBVmoWFSJkWFxX4FFRQWR+LsS4W/rFRb/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////VEFHAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAU291bmRib3kuZGUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMjAwNGh0dHA6Ly93d3cuc291bmRib3kuZGUAAAAAAAAAACU=");
        else
            var snd = new  Audio(WPDM.audio);
        snd.play();
    },

    popupWindow: function(url, title, w, h) {
        /* Fixes dual-screen position                         Most browsers      Firefox */
        var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
        var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;

        var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
        var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

        var left = ((width / 2) - (w / 2)) + dualScreenLeft;
        var top = ((height / 2) - (h / 2)) + dualScreenTop;
        var newWindow = window.open(url, title, 'scrollbars=yes, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);

        /* Puts focus on the newWindow */
        if (window.focus) {
            newWindow.focus();
        }

        return false;
    },

    bootAlert: function(heading, content, width) {
        var html;
        if(!width) width = 400;
        jQuery("#w3eden__bootModal").remove();
        html = '<div class="w3eden" id="w3eden__bootModal"><div id="__bootModal" class="modal fade" tabindex="-1" role="dialog">\n' +
            '  <div class="modal-dialog" style="width: '+width+'px" role="document">\n' +
            '    <div class="modal-content" style="border-radius: 3px;overflow: hidden">\n' +
            '      <div class="modal-header" style="padding: 12px 15px;background: #f5f5f5;">\n' +
            '        <h4 class="modal-title" style="font-size: 9pt;font-weight: 500;padding: 0;margin: 0;font-family:Montserrat, san-serif;letter-spacing: 0.5px">'+heading+'</h4>\n' +
            '      </div>\n' +
            '      <div class="modal-body fetfont" style="line-height: 1.5;text-transform: unset;font-weight:400;letter-spacing:0.5px;font-size: 12px">\n' +
            '        '+content+'\n' +
            '      </div>\n' +
            '      <div class="modal-footer" style="padding: 10px 15px">\n' +
            '        <button type="button" class="btn btn-secondary btn-xs" data-dismiss="modal">Close</button>\n' +
            '      </div>\n' +
            '    </div>\n' +
            '  </div>\n' +
            '</div></div>';
        jQuery('body').append(html);
        jQuery("#__bootModal").modal('show');
    },


    /**
     * Local push notification
     * @param title
     * @param message
     * @param icon
     * @param image
     * @param url
     */
    pushNotify: function(title, message, icon, image, url){
        if (!('Notification' in window) || !('ServiceWorkerRegistration' in window)) {
            return;
        }

        Notification.requestPermission(function (result) {
            if(result === 'granted'){
                console.log('Notification: ' + result);
                try {
                    var notification = new Notification(title, {body: message, icon: icon?icon:'https://cdn1.iconfinder.com/data/icons/hawcons/32/698558-icon-47-note-important-512.png', image: image?image:''});
                    if(url) {
                        notification.onclick = function (e) {
                            e.preventDefault();
                            window.open(url, '_blank');
                        };
                    }
                } catch (err) {
                    console.log('Notification API error: ' + err);
                }
            } else {
                console.log('Notification: ' + result);
            }
        });

    },



    /**
     * Shows notification
     * @param message
     * @param type
     * @param position
     */
    notify: function(message, type, position, autoclose){
        var $ = jQuery;
        if(type === undefined || !type) type = 'info';
        if(position === undefined || !position) position = 'top-right';

        var notifycont = position.indexOf('#') >= 0 ? position : '#wpdm-notify-'+position ;
        if($(notifycont).length  == 0)
            $('body').prepend("<div id='wpdm-notify-"+position+"'></div>");
        var notif = $("<div class='wpdm-notify fetfont wpdm-notify-"+type+"' style='display: none'>"+message+"</div>");
        $(notifycont).append(notif);
        $(notif).fadeIn();
        if(autoclose !== undefined){
            setTimeout(function () {

                $(notif).animate({
                    opacity: 0
                }, 1000, function() {
                    $(this).slideUp();
                });

            }, autoclose);
        }
        return $(notif);
    },

    /**
     * Shows notification
     * @param message
     * @param type
     * @param position
     */
    floatify: function(html, position){
        var $ = jQuery;
        if(position === undefined || !position) position = 'top-right';
        var floatifycont = '#wpdm-floatify-'+position;
        if($(floatifycont).length  == 0)
            $('body').prepend("<div class='w3eden' id='wpdm-floatify-"+position+"'></div>");
        var floatify = $("<div class='wpdm-floatify fetfont style='margin-right: -500px'>"+html+"</div>");
        $(floatifycont).append(floatify);
        $(floatify).animate({marginRight: '0px'});
        return $(floatify);
    },

    blockUI: function(element, xhr){
        jQuery(element).addClass("blockui");
        if(xhr)
            xhr.addEventListener("load", function () {
                jQuery(element).removeClass("blockui");
            });
    },

    unblockUI: function(element){
        jQuery(element).removeClass("blockui");
    },

    overlay: function(element, html){
        var $ = jQuery;
        var overlaycontent = $("<div class='wpdm-overlay-content' style='display: none'>"+html+"<div class='wpdm-overlay-close' style='cursor: pointer'><i class='far fa-times-circle'></i> close</div></div>");
        $(element).addClass('wpdm-overlay').append(overlaycontent);
        $(overlaycontent).fadeIn();
        $('body').on('click', '.wpdm-overlay-close', function () {
            $(overlaycontent).fadeOut(function () {
                $(this).remove();
            });
        });
        return $(overlaycontent);
    },


    confirm: function(heading, content, buttons) {
        var html, $ = jQuery;
        $("#w3eden__boot_popup").remove();
        var _buttons = '';
        if(buttons) {
            _buttons = '<div class="modal-footer" style="padding: 8px 15px;">\n';
            $.each(buttons, function (i, button) {
                var id = 'btx_' + i;
                _buttons += "<button id='" + id + "' class='" + button.class + " btn-xs' style='font-size: 10px;padding: 3px 20px;'>" + button.label + "</button> ";
            });
            _buttons += '</div>\n';
        }

        html = '<div class="w3eden" id="w3eden__boot_popup"><div id="__boot_popup" style="z-index: 9999999 !important;" class="modal fade" tabindex="-1" role="dialog">\n' +
            '  <div class="modal-dialog" role="document" style="max-width: 100%;width: 350px">\n' +
            '    <div class="modal-content" style="border-radius: 3px;overflow: hidden">\n' +
            '      <div class="modal-header" style="padding: 12px 15px;background: #f5f5f5;">\n' +
            '        <h4 class="modal-title" style="font-size: 9pt;font-weight: 500;padding: 0;margin: 0;font-family:var(--wpdm-font), san-serif;letter-spacing: 0.5px">'+heading+'</h4>\n' +
            '      </div>\n' +
            '      <div class="modal-body text-center" style="font-family:var(--wpdm-font), san-serif;letter-spacing: 0.5px;font-size: 10pt;font-weight: 300;padding: 25px;line-height: 1.5">\n' +
            '        '+content+'\n' +
            '      </div>\n' + _buttons +
            '    </div>\n' +
            '  </div>\n' +
            '</div></div>';
        $('body').append(html);
        $("#__boot_popup").modal('show');
        $.each(buttons, function (i, button) {
            var id = 'btx_'+i;
            $('#'+id).unbind('click');
            $( '#'+id).bind('click' , function () {
                button.callback.call($("#__boot_popup"));
                return false;
            });
        });
        return $("#__boot_popup");
    }

};

jQuery(function($){

    // Uploading files
    var file_frame, dfield;

    $('body').on('click', '.wpdm-notify', function () {
        $(this).animate({
            opacity: 0
        }, 1000, function() {
            $(this).slideUp();
        });
    });

    $('body').on('click', '.__wpdm_approvedr' , function( event ) {
        event.preventDefault();
        $btn = $(this);
        $btn.attr('disabled', 'disabled').html('<i class="fa fa-refresh fa-spin"></i>');
        $('.__wpdm_declinedr_'+$btn.data('rid')).remove();
        $.post(ajaxurl,{__approvedr: $(this).data('nonce'), __rid: $(this).data('rid'), action: 'approveDownloadRequest'}, function (res) {
            if(res.match(/ok/)){
                $btn.removeClass('btn-info').addClass('btn-success').html('Approved');
            }
        });
    });

    $('body').on('click', '.txtsc' , function( event ) {
        $(this).select();
    });

    $('body').on('click', '.__wpdm_declinedr' , function( event ) {
        event.preventDefault();
        if(!confirm('Are you sure?')) return false;
        $btn = $(this);
        $btn.attr('disabled', 'disabled').html('<i class="fa fa-refresh fa-spin"></i>');
        $.post(ajaxurl,{__declinedr: $(this).data('nonce'), __rid: $(this).data('rid'), action: 'declineDownloadRequest'}, function (res) {
            if(res.match(/ok/)){
                $('#__emlrow_'+$btn.data('rid')).remove();
            }
        });
    });


    $('body').on('click', '.btn-onclick', function () {
        $(this).css('width', $(this).css('width')).attr('disabled', 'disabled');
        $(this).html($(this).data('onclick'));
    });

    $('body').on('click', '.btn-media-upload' , function( event ){
        event.preventDefault();
        dfield = $($(this).attr('rel'));

        // If the media frame already exists, reopen it.
        if ( file_frame ) {
            file_frame.open();
            return;
        }

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: $( this ).data( 'uploader_title' ),
            button: {
                text: $( this ).data( 'uploader_button_text' )
            },
            multiple: false  // Set to true to allow multiple files to be selected
        });

        // When an image is selected, run a callback.
        file_frame.on( 'select', function() {
            // We set multiple to false so only get one image from the uploader
            attachment = file_frame.state().get('selection').first().toJSON();
            dfield.val(attachment.url);

        });

        // Finally, open the modal
        file_frame.open();
    });

    $('body').on('click', '.btn-image-selector' , function( event ){
        event.preventDefault();
        dfield = $($(this).attr('rel'));
        var dfield_h = $($(this).attr('rel')+'_hidden');

        // If the media frame already exists, reopen it.
        if ( file_frame ) {
            file_frame.open();
            return;
        }

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: $( this ).data( 'uploader_title' ),
            button: {
                text: $( this ).data( 'uploader_button_text' )
            },
            multiple: false  // Set to true to allow multiple files to be selected
        });

        // When an image is selected, run a callback.
        file_frame.on( 'select', function() {
            // We set multiple to false so only get one image from the uploader
            attachment = file_frame.state().get('selection').first().toJSON();
            dfield.attr('src', attachment.url);
            dfield_h.val(attachment.url);

        });

        // Finally, open the modal
        file_frame.open();
    });

    allps = $('#pps_z').val();
    if(allps == undefined) allps = '';
    $('#ps').val(allps.replace(/\]\[/g,"\n").replace(/[\]|\[]+/g,''));
    shuffle = function(){
        var sl = 'abcdefghijklmnopqrstuvwxyz';
        var cl = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        var nm = '0123456789';
        var sc = '~!@#$%^&*()_';
        ps = "";
        pss = "";
        if($('#ls').attr('checked')=='checked') ps = sl;
        if($('#lc').attr('checked')=='checked') ps += cl;
        if($('#nm').attr('checked')=='checked') ps += nm;
        if($('#sc').attr('checked')=='checked') ps +=sc;
        var i=0;
        while ( i <= ps.length ) {
            $max = ps.length-1;
            $num = Math.floor(Math.random()*$max);
            $temp = ps.substr($num, 1);
            pss += $temp;
            i++;
        }

        $('#ps').val(pss);


    };
    $('#gps').click(shuffle);

    $('body').on('click', '#gpsc', function(){
        var allps = "";
        shuffle();
        for(k=0;k<$('#pcnt').val();k++){
            allps += "["+randomPassword(pss,$('#ncp').val())+"]";

        }
        vallps = allps.replace(/\]\[/g,"\n").replace(/[\]|\[]+/g,'');
        $('#ps').val(vallps);

    });

    $('body').on('click', '#pins', function(){
        var aps;
        aps = $('#ps').val();
        aps = aps.replace(/\n/g, "][");
        allps = "["+aps+"]";
        $($(this).data('target')).val(allps);
        tb_remove();
    });

    $('body').on('click', '*:data[toggle="iframe-modal"]', function (e) {
        e.preventDefault();
        var url;
        if ($(this).attr('href') != undefined) url = $(this).attr('href');
        else url = $(this).data('url');
        wpdm_iframe_modal(utl);
    });

});

function randomPassword(chars, size) {

    //var size = 10;
    if(parseInt(size)==Number.NaN || size == "") size = 8;
    var i = 1;
    var ret = "";
    while ( i <= size ) {
        $max = chars.length-1;
        $num = Math.floor(Math.random()*$max);
        $temp = chars.substr($num, 1);
        ret += $temp;
        i++;
    }
    return ret;
}

function __showDownloadLink(pid, fid) {
    var url;
    url = wpdmConfig.siteURL +"?wpdmdl="+pid+"&ind="+fid;
    __bootModal("File Download Link", '<textarea readonly="readonly" class="form-control" style="font-family: monospace">'+url+'</textarea>');
}

function __bootModal(heading, content, width) {
    var html;
    if(!width) width = 400;
    jQuery("#w3eden__bootModal").remove();
    html = '<div class="w3eden" id="w3eden__bootModal"><div id="__bootModal" class="modal fade" tabindex="-1" role="dialog">\n' +
        '  <div class="modal-dialog" style="width: '+width+'px" role="document">\n' +
        '    <div class="modal-content">\n' +
        '      <div class="modal-header">\n' +
        '        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>\n' +
        '        <h4 class="modal-title">'+heading+'</h4>\n' +
        '      </div>\n' +
        '      <div class="modal-body">\n' +
        '        <p>'+content+'</p>\n' +
        '      </div>\n' +
        '      <div class="modal-footer">\n' +
        '        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>\n' +
        '      </div>\n' +
        '    </div>\n' +
        '  </div>\n' +
        '</div></div>';
    jQuery('body').append(html);
    jQuery("#__bootModal").modal('show');
}

function wpdm_boot_popup(heading, content, buttons) {
    var html, $ = jQuery;
    $("#w3eden__boot_popup").remove();
    var _buttons = '<div class="modal-footer" style="padding: 8px 15px;">\n';
    $.each(buttons, function (i, button) {
        var id = 'btx_'+i;
        _buttons += "<button id='"+id+"' class='"+button.class+" btn-xs' style='font-size: 10px;padding: 3px 20px;'>"+button.label+"</button> ";
    });
    _buttons += '</div>\n';

    html = '<div class="w3eden" id="w3eden__boot_popup"><div id="__boot_popup" style="z-index: 9999999 !important;" class="modal fade" tabindex="-1" role="dialog">\n' +
        '  <div class="modal-dialog" role="document" style="max-width: 100%;width: 350px">\n' +
        '    <div class="modal-content" style="border-radius: 3px;overflow: hidden">\n' +
        '      <div class="modal-header" style="padding: 12px 15px;background: #f5f5f5;">\n' +
        '        <h4 class="modal-title" style="font-size: 9pt;font-weight: 500;padding: 0;margin: 0;font-family:Montserrat, san-serif;letter-spacing: 0.5px">'+heading+'</h4>\n' +
        '      </div>\n' +
        '      <div class="modal-body text-center" style="letter-spacing: 0.5px;font-size: 9pt;font-weight: 300;padding: 25px;">\n' +
        '        '+content+'\n' +
        '      </div>\n' + _buttons +
        '    </div>\n' +
        '  </div>\n' +
        '</div></div>';
    $('body').append(html);
    $("#__boot_popup").modal('show');
    $.each(buttons, function (i, button) {
        var id = 'btx_'+i;
        $('#'+id).unbind('click');
        $( '#'+id).bind('click' , function () {
            button.callback.call($("#__boot_popup"));
            return false;
        });
    });
    return $("#__boot_popup");
}

function wpdm_iframe_modal(url) {
    var iframe, $ = jQuery;
    if(url === 'close') {
        $('#wpdm_iframe_modal').remove();
        $('body').removeClass('wpdm-iframe-modal-open');
        return;
    }
    iframe = '<iframe src="'+url+'" style="width: 100%;height: 100%;position: fixed;z-index: 999999999 !important;border: 0;left: 0;top: 0;right: 0;bottom: 0;background: rgba(0,0,0,0.2);display: none;" id="wpdm_iframe_modal"></iframe>';
    $('body').append(iframe).addClass('wpdm-iframe-modal-open');
    $('#wpdm_iframe_modal').fadeIn();

}




