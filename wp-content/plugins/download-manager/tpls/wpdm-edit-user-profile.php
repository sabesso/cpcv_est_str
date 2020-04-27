<?php
global $current_user;
if(!defined('ABSPATH')) die('!');
$store = get_user_meta(get_current_user_id(), '__wpdm_public_profile', true);

?><form method="post" id="edit_profile" name="edit-profile" action="" class="form">
    <div class="card card-default dashboard-card">
        <div class="card-header">
            <a target="_blank" href="<?php echo get_option('__wpdm_author_profile') > 0?get_permalink(get_option('__wpdm_author_profile'))."?user=".$current_user->user_login:get_author_posts_url($current_user->ID);?>" class="btn btn-info btn-sm pull-right" style="margin: 0;margin-top:-5px;margin-right:-7px;"><?php _e( "View Profile" , "download-manager" ); ?></a>
            <?php _e( "Public Profile Info" , "download-manager" ); ?>
        </div>
        <div class="card-body">

            <div class="form-group">
                <label><?php _e( "Title" , "download-manager" ); ?></label>
                <input type="text" value="<?php if (isset($store['title'])) echo $store['title']; ?>" placeholder="" id="" name="__wpdm_public_profile[title]" class="form-control">
            </div>
            <div class="form-group">
                <label><?php _e( "Short Intro" , "download-manager" ); ?></label>
                <input type="text" value="<?php if (isset($store['intro'])) echo $store['intro']; ?>" placeholder="" id="" name="__wpdm_public_profile[intro]" class="form-control">
            </div>
            <div class="form-group">
                <label for="store-logo"><?php _e( "Logo URL" , "download-manager" ); ?></label>
                <div class="input-group">
                    <input type="text" name="__wpdm_public_profile[logo]" id="store-logo" class="form-control" value="<?php echo isset($store['logo']) ? $store['logo'] : ''; ?>"/>
                    <div class="input-group-append">
                        <button class="btn btn-secondary wpdm-media-upload" type="button" rel="#store-logo"><i class="far fa-image"></i></button>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="store-banner"><?php _e( "Banner URL" , "download-manager" ); ?></label>
                <div class="input-group">
                    <input type="text" name="__wpdm_public_profile[banner]" id="store-banner" class="form-control" value="<?php echo isset($store['banner']) ? $store['banner'] : ''; ?>"/>
                    <div class="input-group-append">
                        <button class="btn btn-secondary wpdm-media-upload" type="button" rel="#store-banner"><i class="far fa-image"></i></button>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="store-banner"><?php _e( "Profile Header Text Color" , "download-manager" ); ?></label>

                    <input type="color" name="__wpdm_public_profile[txtcolor]" id="store-banner" class="form-control" value="<?php echo isset($store['txtcolor']) ? $store['txtcolor'] : '#333333'; ?>"/>

            </div>
            <div class="form-group">
                <label><?php _e( "Description" , "download-manager" ); ?></label>
                <textarea type="text" data-placeholder="<?php _e( "Description" , "download-manager" ); ?>" id="" name="__wpdm_public_profile[description]" class="form-control"><?php if (isset($store['description'])) echo $store['description']; ?></textarea>
            </div>
        </div>
    </div>

    <div class="card card-default">
        <div class="card-header">
            <?php _e( "Payment Settings" , "download-manager" ); ?>
        </div>
        <div class="card-body">
            <label><?php _e( "PayPal Email" , "download-manager" ); ?></label>
            <input type="email" value="<?php if (isset($store['paypal'])) echo $store['paypal']; ?>" placeholder="" id="" name="__wpdm_public_profile[paypal]" class="form-control">
        </div>
    </div>

    <div class="well">
        <button id="edit_profile_btn" style="min-width:200px;" type="submit" data-value="<?php _e( "Save Changes" , "download-manager" ); ?>" class="btn btn-primary btn-lg"><i class="far fa-hdd icon-white"></i> <?php _e( "Save Changes" , "download-manager" ); ?></button>
    </div>

</form>
<style>
    .supports-drag-drop{ z-index: 99999999 !important; }
</style>
<script>
    jQuery(function ($) {

        $('#edit_profile').submit(function (e) {
            e.preventDefault();
            $('#edit_profile_btn').html("<i class='fas fa-sun  fa-spin'></i> Please Wait...");
            $(this).ajaxSubmit({
                url: '<?php echo admin_url('admin-ajax.php?action=wpdm_update_public_profile') ?>',
                success: function (res) {
                    if($('#wpdm-dashboard-sidebar #shop-logo') != undefined && $('#store-logo').val() != ''){
                        $('#wpdm-dashboard-sidebar #shop-logo').attr('src', $('#store-logo').val());
                    } else if($('#store-logo').val()!=''){
                        $('#wpdm-dashboard-sidebar').prepend('<img id="shop-logo" style="margin-bottom: 10px;border-radius: 4px" class="thumbnail shop-logo" src="'+ $('#store-logo').val()+'" />');
                    }
                    $('#edit_profile_btn').html("<i class='far fa-hdd'></i> "+$('#edit_profile_btn').data('value'));
                }
            });
        });
    });
</script>
