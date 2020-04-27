
<style>
 .frm td{
     padding:5px;
     border-bottom: 1px solid #eeeeee;

     font-size:10pt;

 }
 h4{
     color: #336699;
     margin-bottom: 0px;
 }
 em{
     color: #888;
 }
.wp-switch-editor{
    height: 27px !important;
}
 </style>


    <div class="row">
        <div class="col-md-12">



            <div class="panel panel-default">
                <div class="panel-heading"><?php echo __('Messages','download-manager'); ?></div>
                <div class="panel-body">


                    <div class="form-group">
                        <label><?php echo __('Permission Denied Message for Packages:','download-manager'); ?></label>
                        <input type=text class="form-control" name="wpdm_permission_msg" value="<?php echo htmlspecialchars(stripslashes_deep(get_option('wpdm_permission_msg','Access Denied'))); ?>" />
                    </div>





                    <div class="form-group">
                        <label><?php echo __('Login Required Message:','download-manager'); ?></label>
                        <textarea class="form-control" cols="70" rows="6" name="wpdm_login_msg"><?php echo htmlspecialchars(get_option('wpdm_login_msg', "<a href=\"".wp_login_url()."\" >Please login to download</a>")); ?></textarea><br>

                    </div>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading"><?php echo __('Server File Browser','download-manager'); ?></div>
                <div class="panel-body">

                    <div class="form-group">
                        <label><?php echo __('Server File Browser Base Dir:','download-manager'); ?></label>
                        <div class="input-group">
                            <input type=text class="form-control" id="_wpdm_file_browser_root" name="_wpdm_file_browser_root" value="<?php echo htmlspecialchars(stripslashes_deep(get_option('_wpdm_file_browser_root', str_replace("\\", "/", ABSPATH)))); ?>" />
                                <span class="input-group-btn">
                                    <button class="btn btn-secondary ttip" title="<?php _e('Reset Base Dir'); ?>" type="button" onclick="jQuery('#_wpdm_file_browser_root').val('<?php echo rtrim(str_replace("\\", "/", ABSPATH),'/'); ?>');"><i class="fas fa-redo"></i></button>
                                </span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><?php echo __('File Browser Access:','download-manager');  ?></label><br/>
                        <input type="hidden" name="_wpdm_file_browser_access[]" value="[NONE]" />
                        <select style="width: 100%" name="_wpdm_file_browser_access[]" multiple="multiple" data-placeholder="<?php _e('Who will have access to server file browser','download-manager'); ?>">
                            <?php

                            $currentAccess = maybe_unserialize(get_option( '_wpdm_file_browser_access', array('administrator')));
                            $selz = '';

                            ?>

                            <?php
                            global $wp_roles;
                            $roles = array_reverse($wp_roles->role_names);
                            foreach( $roles as $role => $name ) {

                                $ro = get_role($role);

                                if(isset($ro->capabilities['edit_posts']) && $ro->capabilities['edit_posts']==1){

                                    if(  $currentAccess ) $sel = (in_array($role,$currentAccess))?'selected=selected':'';
                                    else $sel = '';



                                    ?>
                                    <option  value="<?php echo $role; ?>" <?php echo $sel  ?>> <?php echo $name; ?></option>
                                <?php }} ?>
                        </select>
                    </div>

                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading"><?php echo __('Upload Settings','download-manager'); ?></div>
                <div class="panel-body">
                    <div class="form-group">
                        <input type="hidden" value="0" name="__wpdm_sanitize_filename" />
                        <label><input style="margin: 0 10px 0 0" <?php checked(1, get_option('__wpdm_sanitize_filename',0)); ?> type="checkbox" value="1" name="__wpdm_sanitize_filename"><?php _e('Sanitize Filename','download-manager'); ?></label><br/>
                        <em><?php _e('Check the option if you want to sanitize uploaded file names to remove illegal chars','download-manager'); ?></em>
                        <br/>

                    </div>
                    <hr/>
                    <div class="form-group">
                        <input type="hidden" value="0" name="__wpdm_chunk_upload" />
                        <label><input style="margin: 0 10px 0 0" <?php checked(1, get_option('__wpdm_chunk_upload',0)); ?> type="checkbox" value="1" name="__wpdm_chunk_upload"><?php _e('Chunk Upload','download-manager'); ?></label><br/>
                        <em><?php _e('Check the option if you want to enable chunk upload to override http upload limits','download-manager'); ?></em>
                        <br/>

                    </div>
                    <div class="form-group">
                        <label><?php _e('Chunk Size','download-manager'); ?></label><br/>
                        <div class="input-group">
                            <input class="form-control" value="<?php echo (int)get_option('__wpdm_chunk_size',1024); ?>" type="number"   name="__wpdm_chunk_size">
                            <div class="input-group-addon">KB</div>
                        </div>
                        <br/>

                    </div>

                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading"><?php echo __('File Download','download-manager'); ?></div>
                <div class="panel-body">

                    <div class="form-group">
                        <label><?php echo __('Download Speed:','download-manager'); ?></label>
                        <div class="input-group">
                            <input type=number class="form-control" name="__wpdm_download_speed" value="<?php echo intval(get_option('__wpdm_download_speed',4096)); ?>" />
                            <span class="input-group-addon">KB</span>
                        </div>
                    </div>
                    <br/>
                    <fieldset>
                        <legend><?php echo __('Blocked IPs','download-manager'); ?></legend>
                        <div class="form-group">
                            <textarea placeholder="<?php _e('One IP per line','download-manager'); ?>" rows="5" class="form-control" name="__wpdm_blocked_ips"><?php echo esc_attr(get_option('__wpdm_blocked_ips')); ?></textarea>
                            <em><?php _e('List IP Addresses to blacklist. One IP per line ( Ex: IPv4 - 192.168.23.12 or 192.168.23.1/24 or 192.168.23.* , IPv6 - 2a01:8760:2:3001::1 or 2620:112:3000::/44 )','download-manager'); ?></em>
                        </div>
                        <div class="form-group">
                            <textarea placeholder="<?php _e('Write a Message for Blocked IPs','download-manager'); ?>" class="form-control" name="__wpdm_blocked_ips_msg"><?php echo esc_attr(stripslashes_deep(get_option('__wpdm_blocked_ips_msg'))); ?></textarea>
                            <em><?php _e('Message for Blocked IPs','download-manager'); ?></em>
                        </div>
                    </fieldset>
                    <hr/>
                    <em class="note"><?php _e('If you get broken download, then try enabling/disabling following options, as sometimes server may not support output buffering or partial downloads','download-manager'); ?>:</em>
                    <hr/>
                    <div class="form-group">
                        <label><?php _e('Resumable Downloads','download-manager'); ?></label><br/>
                        <select name="__wpdm_download_resume">
                            <option value="1"><?php _e("Enabled",'download-manager'); ?></option>
                            <option value="2" <?php selected(get_option('__wpdm_download_resume'), 2); ?>><?php _e("Disabled",'download-manager'); ?></option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><?php _e('Output Buffering','download-manager'); ?></label><br/>
                        <select name="__wpdm_support_output_buffer">
                            <option value="1"><?php _e("Enabled",'download-manager'); ?></option>
                            <option value="0" <?php selected(get_option('__wpdm_support_output_buffer'), 0); ?>><?php _e("Disabled",'download-manager'); ?></option>
                        </select>
                    </div>

                    <div class="form-group"><hr/>
                        <input type="hidden" value="0" name="__wpdm_open_in_browser" />
                        <label><input style="margin: 0 10px 0 0" <?php checked(1, get_option('__wpdm_open_in_browser',0)); ?> type="checkbox" value="1" name="__wpdm_open_in_browser"><?php _e('Open in Browser','download-manager'); ?></label><br/>
                        <em><?php _e('Try to Open in Browser instead of download when someone clicks on download link','download-manager'); ?></em>
                        <br/>

                    </div>

                    <fieldset>
                        <legend><?php echo __('reCAPTCHA Lock Settings','download-manager'); ?></legend>
                        <div class="form-group">
                            <label><a name="liappid"></a><?php echo __('reCAPTCHA Site Key','download-manager'); ?></label>
                            <input type="text" class="form-control" name="_wpdm_recaptcha_site_key" value="<?php echo get_option('_wpdm_recaptcha_site_key'); ?>">
                            <em><?php _e('Register a new site for reCAPTCHA from <a target="_blank" href="https://www.google.com/recaptcha/admin#list">here</a>','download-manager'); ?></em>
                        </div>
                        <div class="form-group">
                            <label><a name="liappid"></a><?php echo __('reCAPTCHA Secret Key','download-manager'); ?></label>
                            <input type="text" class="form-control" name="_wpdm_recaptcha_secret_key" value="<?php echo get_option('_wpdm_recaptcha_secret_key'); ?>">
                        </div>
                    </fieldset>




                </div>
            </div>


            <div class="panel panel-default">
                <div class="panel-heading"><?php _e("Misc Settings",'download-manager'); ?></div>
                <div class="panel-body">


                    <div class="form-group">
                        <label for="__wpdm_user_dashboard"><?php echo __('Login Page','download-manager'); ?></label><br/>
                        <?php wp_dropdown_pages(array('name' => '__wpdm_login_url', 'id' => '__wpdm_login_url', 'show_option_none' => __('None Selected','download-manager'), 'option_none_value' => '' , 'selected' => get_option('__wpdm_login_url'))) ?><br/>
                        <em class="note"><?php printf(__('The page where you used short-code %s','download-manager'),'<code>[wpdm_login_form]</code>'); ?> &nbsp; <a target="_blank" href="https://www.wpdownloadmanager.com/doc/short-codes/wpdm_login_form-user-login-form-short-code/"><i title="Read Documentation" class="fa fa-book"></i></a></em>
                        <label style="margin-top: 2px;display: block"><input type="hidden" name="__wpdm_modal_login" value="0"><input <?php checked(1, get_option('__wpdm_modal_login', 0)); ?> style="margin: 0 3px 0 5px" value="1" name="__wpdm_modal_login" type="checkbox" /> <?php _e("Enable modal login form", "download-manager");  ?></label>
                        <em class="note"><?php printf(__('%s will show the modal login form, <a target="_blank" href="%s">follow the docs</a> for moore details','download-manager'),'<code>CTRL + L</code>', 'https://www.wpdownloadmanager.com/how-to-add-modal-login-form-in-your-wordpress-site/'); ?></em>
                        <hr/>
                    </div>

                    <div class="form-group">
                        <label for="__wpdm_user_dashboard"><?php echo __('Register Page','download-manager'); ?></label><br/>
                        <?php wp_dropdown_pages(array('name' => '__wpdm_register_url', 'id' => '__wpdm_register_url', 'show_option_none' => __('None Selected','download-manager'), 'option_none_value' => '' , 'selected' => get_option('__wpdm_register_url'))) ?><br/>
                        <em class="note"><?php printf(__('The page where you used short-code %s','download-manager'),'<code>[wpdm_reg_form]</code>'); ?> &nbsp; <a target="_blank" href="https://www.wpdownloadmanager.com/doc/short-codes/wpdm_reg_form-user-registration-form-short-code/"><i title="Read Documentation" class="fa fa-book"></i></a></em>
                    </div>
                    <div class="form-group">
                        <label for="__wpdm_user_dashboard"><?php echo __('Dashboard Page','download-manager'); ?></label><br/>
                        <?php wp_dropdown_pages(array('name' => '__wpdm_user_dashboard', 'id' => '__wpdm_user_dashboard', 'show_option_none' => __('None Selected','download-manager'), 'option_none_value' => '' , 'selected' => get_option('__wpdm_user_dashboard'))) ?><br/>
                        <em class="note"><?php printf(__('The page where you used short-code %s','download-manager'),'<code>[wpdm_user_dashboard]</code>'); ?> &nbsp; <a target="_blank" href="https://www.wpdownloadmanager.com/doc/short-codes/wpdm_user_dashboard-user-dashboard-short-code/"><i title="Read Documentation" class="fa fa-book"></i></a></em>
                    </div>

                    <div class="form-group"><hr/>
                        <input type="hidden" value="0" name="__wpdm_rss_feed_main" />
                        <label><input style="margin: 0 10px 0 0" type="checkbox" <?php checked(get_option('__wpdm_rss_feed_main'),1); ?> value="1" name="__wpdm_rss_feed_main"><?php _e('Include Packages in Main RSS Feed','download-manager'); ?></label><br/>
                        <em><?php printf(__('Check this option if you want to show wpdm packages in your main <a target="_blank" href="%s">RSS Feed</a>','download-manager'), get_bloginfo('rss_url')); ?></em>
                        <br/>

                    </div>


                    <table cellpadding="5" cellspacing="0" class="frm" width="100%">





                        <?php do_action('basic_settings'); ?>

                    </table>

                </div>
                <div class="panel-footer">

                </div>
            </div>

            <?php do_action('basic_settings_section'); ?>



        </div>
    </div>
