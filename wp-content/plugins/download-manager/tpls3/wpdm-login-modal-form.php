<?php if(!defined('ABSPATH')) die(); ?>
<div class="w3eden">

<!-- Modal Login Form -->
<div class="modal fade" id="wpdmloginmodal" tabindex="-1" role="dialog" aria-labelledby="wpdmloginmodalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-body">
                    <?php
                    if(isset($params['logo']) && $params['logo'] != ''){ ?>
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


                    <form name="loginform" id="modalloginform" action="" method="post" class="login-form" >

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
                                <span class="input-group-addon bg-white"><span class="input-group-text"><i class="fa fa-user"></i></span></span>
                                <input placeholder="<?php _e( "Username or Email" , "download-manager" ); ?>" type="text" name="wpdm_login[log]" id="user_login" class="form-control input-lg required text" value="" size="20" tabindex="38" />
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group input-group-lg">
                                <span class="input-group-addon bg-white"><span class="input-group-text"><i class="fa fa-key"></i></span></span>
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


                        <div class="form-group row login-form-meta-text text-muted mb-3" style="font-size: 10px">
                            <div class="col-sm-5"><label><input class="wpdm-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" /><?php _e( "Remember Me" , "download-manager" ); ?></label></div>
                            <div class="col-sm-7 text-right"><label><a class="color-blue" href="<?php echo wpdm_lostpassword_url(); ?>"><?php _e( "Forgot Password?" , "download-manager" ); ?></a>&nbsp;</label></div>
                        </div>




                        <input type="hidden" name="redirect_to" value="<?php echo $log_redirect; ?>" />

                        <div class="row">
                            <div class="col-lg-12">
                                <button type="submit" name="wp-submit" id="wpdmloginmodal-submit" class="btn btn-block btn-primary btn-lg"><i class="fas fa-user-shield"></i> &nbsp;<?php _e( "Login" , "download-manager" ); ?></button>
                            </div>
                        </div>

                    </form>



                    <?php do_action("wpdm_after_login_form"); ?>

            </div>
            <?php
            $__wpdm_social_login = get_option('__wpdm_social_login');
            $__wpdm_social_login = is_array($__wpdm_social_login)?$__wpdm_social_login:array();
            if(is_array($__wpdm_social_login) && count($__wpdm_social_login) > 1) { ?>

            <div class="modal-footer text-center bg-light p-4">
                <div class="pb-2 text-center d-block" style="width: 100%;margin-bottom: 10px"><?php _e("Or connect using your social account", "download-manager") ?></div>
                <div class="text-center d-block" style="width: 100%">
                    <?php if(isset($__wpdm_social_login['facebook'])){ ?><button type="button" onclick="return _PopupCenter('<?php echo home_url('/?sociallogin=facebook'); ?>', 'Facebook', 400,400);" class="btn btn-social wpdm-facebook wpdm-facebook-connect"><i class="fab fa-facebook-f"></i></button><?php } ?>
                    <?php if(isset($__wpdm_social_login['twitter'])){ ?><button type="button" onclick="return _PopupCenter('<?php echo home_url('/?sociallogin=twitter'); ?>', 'Twitter', 400,400);" class="btn btn-social wpdm-twitter wpdm-linkedin-connect"><i class="fab fa-twitter"></i></button><?php } ?>
                    <?php if(isset($__wpdm_social_login['linkedin'])){ ?><button type="button" onclick="return _PopupCenter('<?php echo home_url('/?sociallogin=linkedin'); ?>', 'LinkedIn', 400,400);" class="btn btn-social wpdm-linkedin wpdm-twitter-connect"><i class="fab fa-linkedin-in"></i></button><?php } ?>
                    <?php if(isset($__wpdm_social_login['google'])){ ?><button type="button" onclick="return _PopupCenter('<?php echo home_url('/?sociallogin=google'); ?>', 'Google', 400,400);" class="btn btn-social wpdm-google-plus wpdm-google-connect"><i class="fab fa-google"></i></button><?php } ?>
                </div>
            </div>

            <?php } ?>
            <?php if(isset($regurl) && $regurl != ''){ ?>
                <div class="modal-footer text-center"><a href="<?php echo $regurl; ?>" name="wp-submit" class="btn btn-block btn-link btn-xs wpdm-reg-link  color-primary"><?php _e( "Don't have an account yet?" , "download-manager" ); ?> <i class="fas fa-user-plus"></i> <?php _e( "Register Now" , "download-manager" ); ?></a></div>
            <?php } ?>
        </div>
    </div>
</div>

</div>

<script>
    jQuery(function ($) {
        var llbl = $('#wpdmloginmodal-submit').html();
        $('#modalloginform').submit(function () {
            $('#wpdmloginmodal-submit').html("<i class='fa fa-spin fa-sync'></i> <?php _e( "Logging In..." , "download-manager" ); ?>");
            $(this).ajaxSubmit({
                success: function (res) {
                    if (!res.success) {
                        $('form .alert-danger').hide();
                        $('#modalloginform').prepend("<div class='alert alert-danger' data-title='<?php _e( "LOGIN FAILED!" , "download-manager" ); ?>'>"+res.message+"</div>");
                        $('#wpdmloginmodal-submit').html(llbl);
                        grecaptcha.reset();
                    } else {
                        $('#wpdmloginmodal-submit').html(wpdm_asset.spinner+" "+res.message);
                        location.href = "<?php echo $log_redirect; ?>";
                    }
                }
            });
            return false;
        });

        $('body').on('click', 'form .alert-danger', function(){
            $(this).slideUp();
        });

        $('body').on('click', '.wpdmloginmodal-trigger', function (e) {
            e.preventDefault();
            $('#wpdmloginmodal').modal('show');
        });
        $('#wpdmloginmodal').on('shown.bs.modal', function () {
            $('#user_login').trigger('focus')
        });
        $(window).keydown(function(event) {
            if(event.ctrlKey && event.keyCode === 76) {

                $('#wpdmloginmodal').modal('show');
                /*console.log("Hey! Ctrl + "+event.keyCode);*/
                event.preventDefault();
            }
        });

    });
</script>
<style>
    .bg-light{
        background: #f5f5f5;
    }
    #wpdmloginmodal .modal-content{
        border: 0;
        box-shadow: 0 0 20px rgba(0,0,0,0.2);
    }
    #wpdmloginmodal .modal-dialog{
        width: 380px;
    }
    #wpdmloginmodal .modal-dialog .modal-body{
        padding: 40px;
    }
    .w3eden .card.card-social-login .card-header{
        font-size: 11px !important;
    }
    #wpdmloginmodal-submit{
        font-size: 12px;
    }
</style>
