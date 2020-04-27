<?php
global $current_user, $wpdb;
$user = get_userdata($current_user->ID);

$member_error = \WPDM\Session::get('member_error');
$member_success = \WPDM\Session::get('member_success');

?>

<div id="edit-profile-form">

    <?php if($member_error){ ?>
        <div class="alert alert-danger" data-title="<?php _e( "SAVE FAILED!" , "download-manager" );?>"><?php echo implode('<br/>',$member_error); \WPDM\Session::clear('member_error'); ?></div>
    <?php } ?>
    <?php if($member_success){ ?>
        <div class="alert alert-success" data-title="<?php _e( "DONE!" , "download-manager" );?>"><?php echo $member_success; \WPDM\Session::clear('member_success'); ?></div>
    <?php } ?>



    <form method="post" id="edit_profile" name="contact_form" action="" class="form">
        <?php wp_nonce_field(NONCE_KEY, '__wpdm_epnonce'); ?>
        <div class="card card-default dashboard-panel">
            <div class="card-header"><?php _e( "Basic Profile" , "download-manager" ); ?></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6"><div class="form-group"><label for="name"><?php _e( "Display name:" , "download-manager" );?> </label><input type="text" class="required form-control" required="required" value="<?php echo $user->display_name;?>" name="wpdm_profile[display_name]" id="name"></div></div>
                    <div class="col-md-6"><div class="form-group"><label for="payment_account"><?php _e( "PayPal Email:" , "download-manager" );?></label><input type="text" value="<?php echo get_user_meta($user->ID,'payment_account',true); ?>" class="form-control" name="payment_account" id="payment_account"> </div></div>

                    <div class="col-md-6"><div class="form-group"><label for="username"><?php _e( "Username:" , "download-manager" );?></label><input type="text" class="required form-control" value="<?php echo $user->user_login;?>" id="username" readonly="readonly"></div></div>
                    <div class="col-md-6"><div class="form-group"><label for="email"><?php _e( "Email:" , "download-manager" );?></label><input type="text" class="required form-control" name="wpdm_profile[user_email]" value="<?php echo $user->user_email;?>" id="email" ></div></div>

                    <div class="col-md-6"><div class="form-group"><label for="new_pass"><?php _e( "New Password:" , "download-manager" );?> </label><input  autocomplete="off" placeholder="<?php _e( "Use nothing if you don't want to change old password" , "download-manager" );?>" type="password" class="form-control" value="" name="password" id="new_pass"> </div></div>
                    <div class="col-md-6"><div class="form-group"><label for="re_new_pass"><?php _e( "Re-type New Password:" , "download-manager" );?> </label><input autocomplete="off" type="password" value="" class="form-control" name="cpassword" id="re_new_pass"> </div></div>


                    <?php do_action('wpdm_update_profile_filed_html', $user); ?>


                </div>
            </div>
        </div>

        <?php do_action("wpdm_edit_profile_form"); ?>


        <div class="row">
            <div class="col-md-12  form-group wpdm-clear"><button type="submit" style="min-width: 250px" class="btn btn-lg btn-primary" id="edit_profile_sbtn"><i class="fas fa-hdd"></i> &nbsp;<?php _e( "Save Changes" , "download-manager" );?></button></div>
        </div>


    </form>
    <div id="edit-profile-msg">
    </div>
</div>

<script>
    jQuery(function ($) {
        $('#edit_profile').on('submit', function (e) {
            e.preventDefault();
            var edit_profile_sbtn = $('#edit_profile_sbtn').html();
            $('#edit_profile_sbtn').html("<i class='fa fa-sync fa-spin'></i> Please Wait...").attr('disabled','disabled');
            $(this).ajaxSubmit({
                success: function (res) {

                    $('#edit-profile-msg').html("<div class='alert alert-"+res.type+"' data-title='"+res.title+"'>"+res.msg+"</div>");
                    $('#edit_profile_sbtn').html(edit_profile_sbtn).removeAttr('disabled');
                }
            });
        });
    });
</script>