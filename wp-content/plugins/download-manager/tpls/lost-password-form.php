<?php
/**
 * Base: wpdmpro
 * Developer: shahjada
 * Team: W3 Eden
 * Date: 19/9/19 11:40
 */

if(!defined("ABSPATH")) die();
?>
<div class="w3eden">
    <div id="wpdmlogin" class="lostpass">
        <form name="loginform" id="resetPassword" action="<?php echo admin_url('/admin-ajax.php?action=resetPassword'); ?>" method="post" class="login-form" >
            <?php wp_nonce_field(NONCE_KEY,'__reset_pass' ); ?>
            <h3 style="margin: 0"><?php _e( "Lost Password?" , "download-manager" ); ?></h3>
            <p>
                <?php _e('Please enter your username or email address. You will receive a link to create a new password via email.', 'download-manager'); ?>
            </p>
            <div class="form-group">
                <input placeholder="<?php _e( "Username or Email" , "download-manager" ); ?>" type="text" name="user_login" id="user_login" class="form-control input-lg required text" value="" size="20" tabindex="38" />
            </div>

            <div class="form-group">
                <button type="submit" name="wp-submit" id="resetPassword-submit" class="btn btn-block btn-info btn-lg"><i class="fa fa-key"></i> &nbsp; <?php _e( "Reset Password" , "download-manager" ); ?></button>
            </div>
            <div class="row">
                <div class="col-md-12 text-center small">
                    <a href="<?php echo home_url('/') ?>" class="color-info btn btn-link btn-xs"><i class="fab fa-fort-awesome-alt"></i> <?php _e("Home", "download-manager"); ?></a> <span class="text-muted">&nbsp; </span>
                    <a href="<?php echo wpdm_login_url(); ?>" class="color-info btn btn-link btn-xs"><i class="fa fa-lock"></i> <?php _e("Login", "download-manager");  ?></a> <span class="text-muted">&nbsp; </span>
                    <a href="<?php echo wpdm_registration_url(); ?>" class="color-info btn btn-link btn-xs"><i class="fa fa-user-plus"></i> <?php _e("Register", "download-manager");  ?></a>
                </div>
            </div>

        </form>
    </div>
</div>
<script>
    jQuery(function ($) {
        var llbl = $('#resetPassword-submit').html();
        $('#resetPassword').submit(function () {
            $('#resetPassword-submit').html("<i class='fa fa-spin fa-sync'></i> <?php _e( "Please Wait..." , "download-manager" ); ?>");
            $(this).ajaxSubmit({
                success: function (res) {

                    if (res.match(/error/)) {
                        $('form .alert').hide();
                        $('#resetPassword').prepend("<div class='alert alert-danger' data-title='<?php _e( "ERROR!" , "download-manager" ); ?>'><?php _e( "Account not found." , "download-manager" ); ?></div>");
                        $('#resetPassword-submit').html(llbl);
                    } else {
                        $('form .alert').hide();
                        $('#resetPassword').prepend("<div class='alert alert-success' data-title='<?php _e( "MAIL SENT!" , "download-manager" ); ?>'><?php _e( "Please check your inbox." , "download-manager" ); ?></div>");
                        $('#resetPassword-submit').html(llbl);
                    }
                }
            });
            return false;
        });

        $('body').on('click', 'form .alert-danger', function(){
            $(this).slideUp();
        });

    });
</script>
