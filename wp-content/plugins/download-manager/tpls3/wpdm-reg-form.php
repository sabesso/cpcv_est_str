<?php if (!defined('ABSPATH')) die('!'); ?>

<div class="w3eden">
    <div class='w3eden' id='wpdmreg'>
        <?php
        if (get_option('users_can_register')) {
            ?>

            <?php
            //LOGO
            if (isset($params['logo']) && $params['logo'] != '' && !isset($nologo)){ ?>
            <div class="text-center wpdmlogin-logo">
                <a href="<?php echo home_url('/'); ?>"><img alt="Logo" src="<?php echo $params['logo']; ?>"/></a>
            </div>
            <?php } ?>

            <form method="post" action="" id="registerform" name="registerform" class="login-form">

                <input type="hidden" name="phash" value="<?php echo isset($regparams) ? $regparams : ''; ?>"/>
                <input type="hidden" id="__reg_nonce" name="__reg_nonce" value=""/>
                <input type="hidden" name="permalink" value="<?php echo $loginurl; ?>"/>

                <?php if (isset($params['note_before']) && trim($params['note_before']) != '') { ?>
                    <div class="alert alert-info alert-note-before mb-3">
                        <?php echo $params['note_before']; ?>
                    </div>
                <?php } ?>

                <?php global $wp_query;
                if (\WPDM\Session::get('reg_error') != '') { ?>
                    <div class="error alert alert-danger"
                         data-title="<?php _e("REGISTRATION FAILED!", "download-manager"); ?>">
                        <?php echo \WPDM\Session::get('reg_error');
                        \WPDM\Session::clear('reg_error'); ?>
                    </div>
                <?php }
                if (!isset($params['social_only']) || (int)$params['social_only'] !== 1) { ?>

                    <div class="form-group row">
                        <div class="col-sm-7">
                            <div class="input-group input-group-lg">
                                <span class="input-group-addon"><i class="fa fa-male"></i></span>
                                <input class="form-control input-lg" required="required"
                                       placeholder="<?php _e("First Name", "download-manager"); ?>" type="text"
                                       size="20" id="first_name"
                                       value="<?php echo isset($tmp_reg_info['first_name']) ? $tmp_reg_info['first_name'] : ''; ?>"
                                       name="wpdm_reg[first_name]">
                            </div>
                        </div>
                        <div class="col-sm-5" style="padding-left: 0">
                            <input class="form-control input-lg" required="required"
                                   placeholder="<?php _e("Last Name", "download-manager"); ?>" type="text" size="20"
                                   id="last_name"
                                   value="<?php echo isset($tmp_reg_info['last_name']) ? $tmp_reg_info['last_name'] : ''; ?>"
                                   name="wpdm_reg[last_name]">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group input-group-lg">
                            <span class="input-group-addon"><i class="fa fa-user"></i></span>
                            <input class="form-control" required="required"
                                   placeholder="<?php _e("Username", "download-manager"); ?>" type="text" size="20"
                                   class="required" id="user_login"
                                   value="<?php echo isset($tmp_reg_info['user_login']) ? $tmp_reg_info['user_login'] : ''; ?>"
                                   name="wpdm_reg[user_login]">
                        </div>
                    </div>
                    <div class="form-group">

                        <div class="input-group input-group-lg">
                            <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                            <input class="form-control input-lg" required="required" type="email" size="25"
                                   placeholder="<?php _e("E-mail", "download-manager"); ?>" id="user_email"
                                   value="<?php echo isset($tmp_reg_info['user_email']) ? $tmp_reg_info['user_email'] : ''; ?>"
                                   name="wpdm_reg[user_email]">
                        </div>
                        <div class="human">
                            <input type="text" placeholder="Retype Email" name="user_email_confirm"
                                   id="user_email_confirm" class="form-control input-lg">
                        </div>

                    </div>

                    <?php if (!isset($params['verifyemail']) || (int)$params['verifyemail'] !== 1) { ?>
                        <div class="form-group row">
                            <div class="col-sm-6">
                                <div class="input-group input-group-lg">
                                    <span class="input-group-addon"><i class="fa fa-key"></i></span>
                                    <input class="form-control"
                                           placeholder="<?php _e("Password", "download-manager"); ?>"
                                           required="required" type="password" size="20" class="required" id="password"
                                           value="" name="wpdm_reg[user_pass]">
                                </div>
                            </div>
                            <div class="col-sm-6" style="padding-left: 0">
                                <input class="form-control input-lg" data-match="#password"
                                       data-match-error="<?php _e("Not Matched!", "download-manager"); ?>"
                                       required="required"
                                       placeholder="<?php _e("Confirm Password", "download-manager"); ?>"
                                       type="password" size="20" class="required" equalto="#password"
                                       id="confirm_user_pass" value="" name="confirm_user_pass">
                            </div>
                        </div>
                    <?php } ?>

                    <?php
                    //Captcha Code
                    if ((int)get_option('__wpdm_recaptcha_regform', 0) === 1 && get_option('_wpdm_recaptcha_site_key') != '') { ?>
                        <div class="form-group row">
                            <div class="col-sm-12">
                                <input type="hidden" id="__recap" name="__recap" value=""/>
                                <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit"
                                        async defer></script>
                                <div id="reCaptchaLock"></div>
                                <script type="text/javascript">
                                    var verifyCallback = function (response) {
                                        jQuery('#__recap').val(response);
                                    };
                                    var widgetId2;
                                    var onloadCallback = function () {
                                        grecaptcha.render('reCaptchaLock', {
                                            'sitekey': '<?php echo get_option('_wpdm_recaptcha_site_key'); ?>',
                                            'callback': verifyCallback,
                                            'theme': 'light'
                                        });
                                    };
                                </script>
                            </div>

                        </div>
                    <?php }
                    //Captcha Code Ends
                    ?>

                    <?php if (isset($params['note_after']) && trim($params['note_after']) != '') { ?>
                        <div class="alert alert-info alter-note-after mb-3">
                            <?php echo $params['note_after']; ?>
                        </div>
                    <?php } ?>


                    <?php do_action("wpdm_register_form"); ?>
                    <?php do_action("register_form"); ?>

                <?php } ?>
                <div class="row">
                    <?php if (!isset($params['social_only']) || (int)$params['social_only'] !== 1) { ?>
                        <div class="col-sm-12">
                            <button type="submit" class="btn btn-success btn-lg btn-block" id="registerform-submit"
                                    name="wp-submit"><i class="fas fa-user-plus"></i>
                                &nbsp;<?php _e("Join Now!", "download-manager"); ?></button>
                        </div>
                    <?php } ?>

                    <?php
                    if (count($__wpdm_social_login) > 1) { ?>
                        <div class="col-sm-12">
                            <div class="text-center panel panel-default" style="margin: 20px 0 0 0">
                                <div class="panel-heading"><?php echo isset($params['social_title']) ? $params['social_title'] : __("Or connect using your social account", "download-manager"); ?></div>
                                <div class="panel-body">
                                    <?php if (isset($__wpdm_social_login['facebook'])) { ?><button type="button" onclick="return _PopupCenter('<?php echo home_url('/?sociallogin=facebook'); ?>', 'Facebook', 400,400);" class="btn btn-social wpdm-facebook wpdm-facebook-connect"><i class="fab fa-facebook-f"></i></button><?php } ?>
                                    <?php if (isset($__wpdm_social_login['twitter'])) { ?><button type="button" onclick="return _PopupCenter('<?php echo home_url('/?sociallogin=twitter'); ?>', 'Twitter', 400,400);" class="btn btn-social wpdm-twitter wpdm-linkedin-connect"><i class="fab fa-twitter"></i></button><?php } ?>
                                    <?php if (isset($__wpdm_social_login['linkedin'])) { ?><button type="button" onclick="return _PopupCenter('<?php echo home_url('/?sociallogin=linkedin'); ?>', 'LinkedIn', 400,400);" class="btn btn-social wpdm-linkedin wpdm-twitter-connect"><i  class="fab fa-linkedin-in"></i></button><?php } ?>
                                    <?php if (isset($__wpdm_social_login['google'])) { ?><button type="button" onclick="return _PopupCenter('<?php echo home_url('/?sociallogin=google'); ?>', 'Google', 400,400);" class="btn btn-social wpdm-google-plus wpdm-google-connect"><i class="fab fa-google"></i></button><?php } ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                    <?php if ($loginurl != '') { ?>
                        <div class="col-sm-12">
                            <br/>
                            <a href="<?php echo $loginurl; ?>"
                               class="btn btn-link btn-xs btn-block wpdm-login-link color-success"
                               id="registerform-login"
                               name="wp-submit"><?php _e("Already have an account?", "download-manager"); ?> <i
                                        class="fa fa-lock"></i> <?php _e("Login", "download-manager"); ?></a>
                        </div>
                    <?php } ?>

                </div>


            </form>


            <script>
                jQuery(function ($) {
                    $('#__reg_nonce').val('<?php echo wp_create_nonce(NONCE_KEY); ?>');
                    $.getScript('<?php echo WPDM_BASE_URL . 'assets/js/validator.min.js'; ?>', function () {
                        $('#registerform').validator();
                    });
                    var llbl = $('#registerform-submit').html();
                    $('#registerform').submit(function () {
                        <?php  if(!isset($params['captcha']) || $params['captcha'] == 'true'){ ?>
                        if ($('#__recap').val() == '') {
                            WPDM.beep();
                            WPDM.notify("Invalid CAPTCHA!", 'error');
                            return false;
                        }
                        <?php } ?>
                        $('#registerform-submit').html("<i class='fa fa-spin fa-spinner'></i> <?php _e("Please Wait...", "download-manager"); ?>");
                        $(this).ajaxSubmit({
                            success: function (res) {
                                if (res.success == false) {
                                    $('form .alert-danger').hide();
                                    $('#registerform').prepend("<div class='alert alert-danger'>" + res.message + "</div>");
                                    $('#registerform-submit').html(llbl);
                                } else {
                                    $('#registerform-submit').html("<i class='fa fa-check-circle'></i> <?php _e("Success! Redirecting...", "download-manager"); ?>");
                                    location.href = "<?php echo $reg_redirect; ?>";
                                }
                            }
                        });
                        return false;
                    });
                });
            </script>
            <style>
                #reCaptchaLock iframe {
                    transform: scaleX(1.22);
                    margin-left: 33px;
                }
            </style>

        <?php } else echo "<div class='alert alert-warning'>" . __("Registration is disabled!", "download-manager") . "</div>"; ?>
    </div>
</div>
