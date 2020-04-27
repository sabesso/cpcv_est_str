<?php

/**
 *
 */

namespace WPDM;


use WPDM\libs\Crypt;
use WPDM\libs\FileSystem;

class MediaAccessControl
{
    function __construct()
    {
        add_action('init', array($this, 'mediaDownload'));
        add_action('wp_ajax_wpdm_media_pass', array($this, 'makeMediaPass'));
        add_action('wp_ajax_nopriv_wpdm_media_pass', array($this, 'makeMediaPass'));

        if(is_admin()) {
            add_filter('attachment_fields_to_edit', array($this, 'protectionSettings'), null, 2);
            add_action('wp_ajax_wpdm_media_access', array($this, 'mediaAccessControl'));
            add_action('wp_ajax_make_media_public', array($this, 'makeMediaPublic'));
            add_action('wp_ajax_make_media_private', array($this, 'makeMediaPrivate'));
            add_action('admin_footer', array($this, 'footerScripts'));
        }
        else {
            add_action("init", array($this, 'protectMediaLibrary'), 8);
        }


    }

    function mediaDownload(){
        if(isset($_REQUEST['__mediakey'])){
            $mediaid = TempStorage::get('__wpdm_meida_key_'.wpdm_query_var('__mediakey', 'txt'));
            if($mediaid > 0) {
                $file = get_attached_file($mediaid);
                $file = apply_filters("wpdm_media_download", $file, $mediaid);
                FileSystem::downloadFile($file, basename($file), 10240, 0, array('play' => 1));
                die();
            }
        }
    }


    function makeMediaPass(){
        if(wpdm_query_var('__xnonce') && wp_verify_nonce(wpdm_query_var('__xnonce'), NONCE_KEY)){
            $mediaid = Crypt::decrypt(wpdm_query_var('__meida'));
            $password = get_post_meta($mediaid, '__wpdm_media_pass', true);
            if($password === wpdm_query_var('__pswd')){
                $mediakey = uniqid();
                $xpire_sex = (int)get_option('__wpdm_private_link_expiration_period') * (int)get_option('__wpdm_private_link_expiration_period_unit');
                $xpire_sex = $xpire_sex > 0 ? $xpire_sex :  30;
                TempStorage::set('__wpdm_meida_key_'.$mediakey, $mediaid, $xpire_sex);
                wp_send_json(array('success' => true, '__mediakey' => $mediakey));
            }
        }
        wp_send_json(array('success' => false, 'error' => __( "<b>Error:</b> Wrong Password! Try Again.", "download-manager" )));
    }


    function protectMediaLibrary(){
        if(isset($_REQUEST['wpdmmediaid'])){
            global $wpdb, $current_user;
            $media = get_post($_REQUEST['wpdmmediaid']);
            $media_meta = wp_get_attachment_metadata($_REQUEST['wpdmmediaid']);
            //wpdmdd($media_meta);
            //wpdmdd($media);
            $media->path = str_replace(home_url('/'), ABSPATH.'/', $media->guid);
            $media->filesize = wpdm_file_size($media->path);

            $access = get_post_meta($media->ID, '__wpdm_media_access', true);
            $password = get_post_meta($media->ID, '__wpdm_media_pass', true);
            $private = get_post_meta($media->ID, '__wpdm_private', true);
            if(current_user_can('manage_options')) $private = false;
            $user_roles = is_user_logged_in() ? $current_user->roles + array('public') : array();
            $user_roles[] = 'public';
            $user_allowed = array_intersect($user_roles, $access);
            $user_allowed = count($user_allowed);
            if( $private && ( $password || !$user_allowed ) ) {
                $picon = wp_get_attachment_image($media->ID, 'thumbnail', true);
                $keyvalid = true;
                $__hash = Crypt::encrypt($media->ID);
                $download_url = "";
                include wpdm_tpl_path("media-download.php");
                die();
            }

            $upload_dir = wp_upload_dir();
            $file_path = $upload_dir['basedir'].'/'.$_REQUEST['wpdmmedia'];
            $file_path = apply_filters("wpdm_media_download", $file_path, $media->ID);
            FileSystem::downloadFile($file_path, basename($file_path), 10240, 0, array('play' => 1));
            die();
        }
    }

    function updateMediaAccess(){
        $protected = get_option("__wpdm_media_private");
        $protected = $protected ? (array)json_decode($protected) : array();
        $upload_dir = wp_upload_dir();
        $upload_dir = $upload_dir['basedir'];
        $htaccess_rules = "<IfModule mod_rewrite.c>\nRewriteEngine On\n";
        foreach ($protected as $id => $path){
           $path = str_replace($upload_dir.'/', "", $path);
           $htaccess_rules .= "RewriteRule ^({$path})$ ../../index.php?wpdmmediaid={$id}&wpdmmedia=$1\n";
        }
        $htaccess_rules .= "</IfModule>";
        file_put_contents($upload_dir.'/.htaccess', $htaccess_rules);
    }

    function makeMediaPrivate(){
        if(current_user_can('edit_posts')) {
            $id = wpdm_query_var('mediaid');
            //$meta = wp_get_attachment_metadata($id);
            //wpdmdd($meta);
            update_post_meta($id, '__wpdm_media_access', wpdm_query_var('media_access'));
            update_post_meta($id, '__wpdm_media_pass', wpdm_query_var('media_pass'));
            update_post_meta($id, '__wpdm_private', 1);
            $protected = get_option("__wpdm_media_private");
            $protected = $protected ? (array)json_decode($protected) : array();
            $protected[$id] = get_attached_file($id);
            $protected = json_encode($protected);
            update_option('__wpdm_media_private', $protected, 'no');
            do_action("wpdm_make_media_private");
            $this->updateMediaAccess();
        }
        $this->mediaAccessControl();
    }

    function makeMediaPublic(){
        if(current_user_can('edit_posts')) {
            $id = wpdm_query_var('mediaid');
            delete_post_meta($id, '__wpdm_media_access');
            delete_post_meta($id, '__wpdm_media_pass');
            delete_post_meta($id, '__wpdm_private');
            $protected = get_option("__wpdm_media_private");
            $protected = $protected ? (array)json_decode($protected) : array();
            unset($protected[$id]);
            $protected = json_encode($protected);
            update_option('__wpdm_media_private', $protected, 'no');
            do_action("wpdm_make_media_public");
            $this->updateMediaAccess();
        }
        $this->mediaAccessControl();
    }

    function mediaAccessControl(){
        $id = wpdm_query_var('mediaid');
        $wpdm_media_access = maybe_unserialize(get_post_meta($id, '__wpdm_media_access', true));
        $wpdm_media_pass = get_post_meta($id, '__wpdm_media_pass', true);
        $wpdm_media_private = (int)get_post_meta($id, '__wpdm_private', true);
        ?>

        <div class="panel panel-default" id="__protm"  <?php if ($wpdm_media_private){ ?> style="display: none" <?php } ?>>
            <div class="panel-body">
                <?php _e('This file is not protected.', 'download-manager') ?>
            </div>
            <div class="panel-footer">
                <button class="btn btn-success btn-block" onclick="jQuery('#__protm').slideUp();jQuery('#__prots').slideDown();"><?php _e('Protect this file', 'download-manager'); ?></button>
            </div>
        </div>

        <div id="__prots" class="panel panel-default" <?php if (!$wpdm_media_private){ ?> style="display: none" <?php } ?>>
            <?php if ($wpdm_media_private){ ?><div class="panel-body text-danger"><i class="fa fa-lock"></i> &mdash; <?php echo __( "This file is protected" , "download-manager" ); ?></div><?php } ?>
            <div class="panel-footer">
                <button class="btn btn-block btn-primary btn-sm" id="__makeprivate" data-id="<?php echo $id; ?>"><?php _e('Block Direct Access', 'download-manager'); ?></button>
            </div>
            <div class="panel-footer">
                <button class="btn btn-block btn-danger btn-sm" id="__makepublic" data-id="<?php echo $id; ?>"><?php _e('Remove Protection', 'download-manager'); ?></button>
            </div>
        </div>


        <style>
            #acx{
                height: 150px;overflow: auto;
            }
            #acx input[type=checkbox]{
                transform: scale(0.7);
                margin-top: -1px;
            }
            #acx label{
                font-weight: 400;
                padding: 0 15px;
                line-height: 18px;
                font-size: 10px;
                display: block;
                width: 100%;
            }
        </style>
        <?php
        die();
    }

    function footerScripts(){
        global $pagenow;
        ?>
        <script>
            var xhr = null;
            jQuery(function ($) {
                /*$('body').on('click', '.media-modal-content .attachment', function () {
                    //if(xhr) xhr.abort();
                    //$('.attachment-info').after("<div class='w3eden'><div class='panel panel-default'><div class='panel-body'><?php echo __( "This file is not protected yet!", "download-manager" ); ?></div><div class='panel-footer'><button type='button' class='btn btn-success btn-block'>Protect It!</button></div></div></div><hr/>");
                    $('.w3eden.media-access-control-container').remove();
                    $('.attachment-info').after("<div class='w3eden media-access-control-container'><div  id='wpdm-media-access'><div class='panel panel-default'><div class='panel-body'><i class='fa fa-sun fa-spin'></i> <?php echo __( "Checking status...", "download-manager" ); ?></div></div></div><hr/></div>");
                    xhr = $.ajax({
                        type: "GET",
                        url: ajaxurl,
                        data: "action=wpdm_media_access&mediaid="+$(this).data('id'),
                        success: function(res){
                            $('#wpdm-media-access').html(res);
                        }
                    });
                });

                $('body').on('click', '#wp-media-grid .attachment', function () {
                    //if(xhr) xhr.abort();
                    //$('.attachment-info').after("<div class='w3eden'><div class='panel panel-default'><div class='panel-body'><?php echo __( "This file is not protected yet!", "download-manager" ); ?></div><div class='panel-footer'><button type='button' class='btn btn-success btn-block'>Protect It!</button></div></div></div><hr/>");
                    $('.w3eden.media-access-control-container').remove();
                    $('.media-modal-content .attachment-info .details').after("<div class='w3eden media-access-control-container'><div  id='wpdm-media-access'><div class='panel panel-default'><div class='panel-body'><i class='fa fa-sun fa-spin'></i> <?php echo __( "Checking status...", "download-manager" ); ?></div></div></div><hr/></div>");
                    xhr = $.ajax({
                        type: "GET",
                        url: ajaxurl,
                        data: "action=wpdm_media_access&mediaid="+$(this).data('id'),
                        success: function(res){
                            $('#wpdm-media-access').html(res);
                        }
                    });
                });*/

                $('body').on('click', '#__makepublic', function () {
                    $('#__prots').addClass('blockui');
                    $.post(ajaxurl, { action: 'make_media_public', mediaid: $(this).data('id') }, function(res){
                        $('#__prots').removeClass('blockui');
                        $('#__prots').slideUp();
                        $('#__protm').slideDown(function () {
                            $('#wpdm-media-access').html(res);
                        });
                    });
                });

                $('body').on('click', '#__makeprivate', function () {
                    $('#__prots').addClass('blockui');
                    var media_access = $("input.media_access:checkbox:checked").map(function(){
                        return $(this).val();
                    }).get();
                    $.post(ajaxurl, { action: 'make_media_private', mediaid: $(this).data('id'), media_pass: $('#media_pass').val(), media_access: media_access }, function(res){
                        $('#__prots').removeClass('blockui');
                        $('#wpdm-media-access').html(res);
                    });
                });
            });
        </script>
        <?php
    }

    function protectionSettings($form_fields, $post) {
        ob_start();
        //wpdmprecho($post);
        ?><script>
            jQuery(function ($) {
                $('.w3eden.media-access-control-container').remove();
                $('.attachment-info .details').after("<br style='clear:both;'/><hr style='clear:both;margin-bottom:10px'/><div class='w3eden media-access-control-container'><div  id='wpdm-media-access'><div class='panel panel-default'><div class='panel-body'><i class='fa fa-sun fa-spin'></i> <?php echo __( "Checking status...", "download-manager" ); ?></div></div></div></div>");
                xhr = $.ajax({
                    type: "GET",
                    url: ajaxurl,
                    data: "action=wpdm_media_access&mediaid=<?php echo $post->ID; ?>", //+$(this).data('id'),
                    success: function(res){
                        $('#wpdm-media-access').html(res);
                    }
                });
            });
        </script><?php
        $html = ob_get_clean();
        $form_fields["mac"] = array(
            "label" => '',
            "input" => "html", // this is default if "input" is omitted
            "html" => $html
        );



        return $form_fields;
    }


}

