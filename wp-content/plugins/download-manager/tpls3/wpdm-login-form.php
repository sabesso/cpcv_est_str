<?php

if(!defined('ABSPATH')) die();

?>
<div class="w3eden">
    <div id="wpdmlogin">

        <?php if(isset($params['logo']) && $params['logo'] != '' && !is_user_logged_in()){ ?>
            <div class="text-center wpdmlogin-logo">
                <a href="<?php echo home_url('/'); ?>"><img alt="Logo" src="<?php echo $params['logo'];?>" /></a>
            </div>
        <?php } ?>

        <?php if(\WPDM\Session::get('reg_warning')): ?>  <br>

            <div class="alert alert-warning" data-title="WARNING!" align="center" style="font-size:10pt;">
                <?php echo \WPDM\Session::get('reg_warning'); \WPDM\Session::clear('reg_warning'); ?>
            </div>

        <?php endif; ?>

        <?php if(\WPDM\Session::get( 'sccs_msg' )): ?><br>

            <div class="alert alert-success" data-title="DONE!" align="center" style="font-size:10pt;">
                <?php echo \WPDM\Session::get( 'sccs_msg' );  \WPDM\Session::clear( 'sccs_msg' ); ?>
            </div>

        <?php endif; ?>


        <?php do_action("wpdm_before_login_form"); ?>


        <form name="loginform" id="loginform" action="" method="post" class="login-form" >

            <input type="hidden" name="permalink" value="<?php the_permalink(); ?>" />

            <?php global $wp_query; if(\WPDM\Session::get('login_error')) {  ?>
                <div class="error alert alert-danger" >
                    <b><?php _e( "Login Failed!" , "download-manager" ); ?></b><br/>
                    <?php echo preg_replace("/<a.*?<\/a>\?/i","",\WPDM\Session::get('login_error')); \WPDM\Session::clear('login_error'); ?>
                </div>
            <?php } ?>

            <?php  if(isset($params['note_before'])) {  ?>
                <div class="alert alert-info alert-note-before mb-3" >
                    <?php echo $params['note_before']; ?>
                </div>
            <?php } ?>

            <div class="form-group">
                <div class="input-group input-group-lg">
                    <span class="input-group-addon" id="sizing-addon1"><i class="fa fa-user"></i></span>
                    <input placeholder="<?php _e( "Username or Email" , "download-manager" ); ?>" type="text" name="wpdm_login[log]" id="user_login" class="form-control input-lg required text" value="" size="20" tabindex="38" />
                </div>
            </div>
            <div class="form-group">
                <div class="input-group input-group-lg">
                    <span class="input-group-addon" id="sizing-addon1"><i class="fa fa-key"></i></span>
                    <input type="password" placeholder="<?php _e( "Password" , "download-manager" ); ?>" name="wpdm_login[pwd]" id="user_pass" class="form-control input-lg required password" value="" size="20" tabindex="39" />
                </div>
            </div>

            <?php if((int)get_option('__wpdm_recaptcha_loginform', 0) === 1 && get_option('_wpdm_recaptcha_site_key') != ''){ ?>
                <div class="form-group">
                    <input type="hidden" id="__recap" name="__recap" value="" />
                    <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>
                    <div  id="reCaptchaLock"></div>
                    <script type="text/javascript">
                        var verifyCallback = function(response) {
                            jQuery('#__recap').val(response);
                        };
                        var widgetId2;
                        var onloadCallback = function() {
                            grecaptcha.render('reCaptchaLock', {
                                'sitekey' : '<?php echo get_option('_wpdm_recaptcha_site_key'); ?>',
                                'callback' : verifyCallback,
                                'theme' : 'light'
                            });
                        };
                    </script>
                </div>
                <style> #reCaptchaLock iframe { transform: scaleX(1.23); margin-left: 33px; } </style>
            <?php }  ?>

            <?php  if(isset($params['note_after'])) {  ?>
                <div class="alert alert-info alter-note-after mb-3" >
                    <?php echo $params['note_after']; ?>
                </div>
            <?php } ?>

            <?php do_action("wpdm_login_form"); ?>
            <?php do_action("login_form"); ?>

            <div class="row login-form-meta-text text-muted" style="margin-bottom: 10px">
                <div class="col-md-5"><label><input class="wpdm-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" /><?php _e( "Remember Me" , "download-manager" ); ?></label></div>
                <div class="col-md-7 text-right"><label><a class="color-blue" href="<?php echo wpdm_lostpassword_url(); ?>"><?php _e( "Forgot Password?" , "download-manager" ); ?></a>&nbsp;</label></div>
            </div>
            <div class="row">
                <div class="col-md-12"><button type="submit" name="wp-submit" id="loginform-submit" class="btn btn-block btn-primary btn-lg"><i class="fas fa-user-shield"></i> &nbsp;<?php _e( "Login" , "download-manager" ); ?></button></div>
                <div class="col-md-12">
                    <?php if(is_array($__wpdm_social_login) && count($__wpdm_social_login) > 1) { ?>
                        <div class="text-center panel panel-default" style="margin: 20px 0 0 0">
                            <div class="panel-heading"><?php _e("Or connect using your social account", "download-manager") ?></div>
                            <div class="panel-body">
                                <?php if(isset($__wpdm_social_login['facebook'])){ ?><button type="button" onclick="return _PopupCenter('<?php echo home_url('/?sociallogin=facebook'); ?>', 'Facebook', 400,400);" class="btn btn-social wpdm-facebook wpdm-facebook-connect"><i class="fab fa-facebook-f"></i></button><?php } ?>
                                <?php if(isset($__wpdm_social_login['twitter'])){ ?><button type="button" onclick="return _PopupCenter('<?php echo home_url('/?sociallogin=twitter'); ?>', 'Twitter', 400,400);" class="btn btn-social wpdm-twitter wpdm-linkedin-connect"><i class="fab fa-twitter"></i></button><?php } ?>
                                <?php if(isset($__wpdm_social_login['linkedin'])){ ?><button type="button" onclick="return _PopupCenter('<?php echo home_url('/?sociallogin=linkedin'); ?>', 'LinkedIn', 400,400);" class="btn btn-social wpdm-linkedin wpdm-twitter-connect"><i class="fab fa-linkedin-in"></i></button><?php } ?>
                                <?php if(isset($__wpdm_social_login['google'])){ ?><button type="button" onclick="return _PopupCenter('<?php echo home_url('/?sociallogin=google'); ?>', 'Google', 400,400);" class="btn btn-social wpdm-google-plus wpdm-google-connect"><i class="fab fa-google"></i></button><?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <?php if($regurl != ''){ ?>
                    <div class="col-md-12"><br/><a href="<?php echo $regurl; ?>" name="wp-submit" id="loginform-submit" class="btn btn-block btn-link btn-xs wpdm-reg-link  color-primary"><?php _e( "Don't have an account yet?" , "download-manager" ); ?> <i class="fas fa-user-plus"></i> <?php _e( "Register Now" , "download-manager" ); ?></a></div>
                <?php } ?>
            </div>


            <input type="hidden" name="redirect_to" value="<?php echo $log_redirect; ?>" />



        </form>

        <?php do_action("wpdm_after_login_form"); ?>

    </div>
</div>

<script>
    jQuery(function ($) {
        var llbl = $('#loginform-submit').html();
        $('#loginform').submit(function () {
            $('#loginform-submit').html("<i class='fa fa-spin fa-sync'></i> <?php _e( "Logging In..." , "download-manager" ); ?>");
            $(this).ajaxSubmit({
                success: function (res) {
                    if (!res.success) {
                        $('form .alert-danger').hide();
                        $('#loginform').prepend("<div class='alert alert-danger' data-title='<?php _e( "LOGIN FAILED!" , "download-manager" ); ?>'>"+res.message+"</div>");
                        $('#loginform-submit').html(llbl);
                        <?php if((int)get_option('__wpdm_recaptcha_loginform', 0) === 1 && get_option('_wpdm_recaptcha_site_key') != ''){ ?>
                        grecaptcha.reset();
                        <?php } ?>
                    } else {
                        $('#loginform-submit').html(wpdm_asset.spinner+" "+res.message);
                        location.href = "<?php echo $log_redirect; ?>";
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
