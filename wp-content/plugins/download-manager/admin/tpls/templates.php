

<div class="wrap w3eden">

<div class="panel panel-default" id="wpdm-wrapper-panel">
<div class="panel-heading">
<b><i class="fa fa-magic color-purple"></i> &nbsp; <?php echo __( "Templates" , "download-manager" ); ?></b>

    <div style="clear: both"></div>
</div>
    <ul id="tabs" class="nav nav-tabs nav-wrapper-tabs" style="padding: 60px 10px 0 10px;background: #f5f5f5">
    <li <?php if(!isset($_GET['_type'])||$_GET['_type']=='link'){ ?>class="active"<?php } ?>><a href="edit.php?post_type=wpdmpro&page=templates&_type=link" id="basic"><?php _e( "Link Templates" , "download-manager" ); ?></a></li>
    <li <?php if(isset($_GET['_type'])&&$_GET['_type']=='page'){ ?>class="active"<?php } ?>><a href="edit.php?post_type=wpdmpro&page=templates&_type=page" id="basic"><?php _e( "Page Templates" , "download-manager" ); ?></a></li>
    <li <?php if(isset($_GET['_type'])&&$_GET['_type']=='email'){ ?>class="active"<?php } ?>><a href="edit.php?post_type=wpdmpro&page=templates&_type=email" id="basic"><?php _e( "Email Templates" , "download-manager" ); ?></a></li>
    </ul>
<div class="tab-content panel-body">
    <?php if(!isset($_GET['_type']) || $_GET['_type']!='email'){ ?>
<blockquote  class="alert alert-info" style="margin-bottom: 10px">
<?php echo sprintf(__( "Custom Template editor is available with <a target='_blank' href='%s'>WordPress Download Manager Pro</a>" , "download-manager" ), 'https://www.wpdownloadmanager.com/pricing/'); ?>
</blockquote>
    <?php } ?>


<table cellspacing="0" class="table table-hover">
    <thead>
    <tr>
    <th style="min-width: 400px"><?php echo __( "Template Name" , "download-manager" ); ?></th>
    <th style="width: 250px;"><?php echo __( "Template ID" , "download-manager" ); ?></th>

    <th style="width: 260px;text-align: right"><?php echo __( "Actions" , "download-manager" ); ?></th>
    </tr>
    </thead>


    <tbody class="list:post" id="the-list">

    <?php 
    $ttype = isset($_GET['_type'])?esc_attr($_GET['_type']):'link';
    if($ttype != 'email'){
        $ctpls = WPDM\admin\menus\Templates::Dropdown(array('data_type' => 'ARRAY', 'type' => $ttype));
        $ctemplates = maybe_unserialize(get_option("_fm_{$ttype}_templates",true));
    if(is_array($ctemplates))
        $ctemplates = array_keys($ctemplates);
    if(!is_array($ctemplates)) $ctemplates = array();
    $tplstatus = maybe_unserialize(get_option("_fm_{$ttype}_template_status"));

    foreach($ctpls as $ctpl => $title){
        $tplid = str_replace(".php","",$ctpl);
        $status = isset($tplstatus[$tplid])?$tplstatus[$tplid]:1;
    ?>
     
    <tr valign="top" class="author-self status-inherit" id="template-<?php echo $ttype; ?>-<?php echo $ctpl; ?>">
                <td class="column-icon media-icon" style="text-align: left;">                                     
                   <nobr><?php echo $title; ?></nobr>
                </td>
                <td>
                <input class="form-control input-sm input-tplid" type="text" readonly="readonly" onclick="this.select()" value="<?php echo $tplid; ?>" />
                </td>

        <td style="text-align: right">
            <a data-toggle="modal" href="#" data-href="admin-ajax.php?action=template_preview&_type=<?php echo $ttype; ?>&template=<?php echo $ctpl; ?>" data-target="#preview-modal" rel="<?php echo $ctpl; ?>" class="template_preview btn btn-sm btn-success"><i class="fa fa-desktop"></i> Preview</a>
        </td>
                
     
     </tr>
    <?php
    }} else {
        $templates = \WPDM\Email::templates();
    foreach($templates as $ctpl => $template){
        ?>
        <tr valign="top" class="author-self status-inherit" id="post-8">
            <td class="column-icon media-icon" style="text-align: left;">
                <?php echo $template['label']; ?> ( <?php _e( "To:" , "download-manager" ); ?> <?php echo ucfirst($template['for']); ?> )

            </td>
            <td>
                <?php echo $ctpl; ?>
            </td>
            <td style="text-align: right">

    <a href="edit.php?post_type=wpdmpro&page=templates&_type=email&task=EditEmailTemplate&id=<?php echo $ctpl; ?>" class="btn btn-sm btn-primary"><i class="fas fa-pencil-alt"></i> <?php echo __( "Edit" , "download-manager" ); ?></a>

            </td>


        </tr>
    <?php
    }}
    ?>
    </tbody>
</table>



    <?php if($ttype == 'email'){ ?>
    <form method="post" id="emlstform">
        <?php wp_nonce_field(NONCE_KEY, '__wpdm_nonce') ?>
        <div class="panel panel-default">
            <div class="panel-heading">Email Settings</div>
            <div class="panel-body">

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <?php _e( "Email Template" , "download-manager" ); ?>
                            <select name="__wpdm_email_template" class="form-control wpdm-custom-select" style="width: 200px" id="etmpl">
                                <?php
                                $eds = \WPDM\libs\FileSystem::scanDir(WPDM_BASE_DIR.'email-templates');
                                $__wpdm_email_template = get_option('__wpdm_email_template', "default.html");
                                $__wpdm_email_setting = maybe_unserialize(get_option('__wpdm_email_setting'));
                                foreach ($eds as $file) {
                                    if(strstr($file, ".html")) {
                                        ?>
                                        <option value="<?php echo basename($file); ?>" <?php selected($__wpdm_email_template, basename($file)); ?> ><?php echo ucfirst(str_replace(".html", "", basename($file))); ?></option>
                                        <?php
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <?php _e( "Logo URL" , "download-manager" ); ?>
                            <?php echo wpdm_media_field(array('placeholder' => __("Logo URL" , "download-manager"), 'name' => '__wpdm_email_setting[logo]', 'id' => 'logo-url', 'value' => (isset($__wpdm_email_setting['logo'])?$__wpdm_email_setting['logo']:''))); ?>
                        </div>
                        <div class="form-group">
                            <?php _e( "Banner/Background Image URL" , "download-manager" ); ?>
                            <?php echo wpdm_media_field(array('placeholder' => __("Banner/Background Image URL" , "download-manager"), 'name' => '__wpdm_email_setting[banner]', 'id' => 'banner-url', 'value' => (isset($__wpdm_email_setting['banner'])?$__wpdm_email_setting['banner']:''))); ?>
                            <div class="xbubble" style="margin-top: 5px;box-shadow: none;z-index: 999">
                                <img class="bselect" src="https://wpdmcdn.s3.amazonaws.com/emails/brush.jpg" style="height: 32px;margin: 2px" />
                                <img class="bselect" src="https://wpdmcdn.s3.amazonaws.com/emails/a.jpg" style="height: 32px;margin: 2px" />
                                <img class="bselect" src="https://wpdmcdn.s3.amazonaws.com/emails/crain.jpg" style="height: 32px;margin: 2px" />
                                <img class="bselect" src="https://wpdmcdn.s3.amazonaws.com/emails/c.jpg" style="height: 32px;margin: 2px" />
                                <img class="bselect" src="https://wpdmcdn.s3.amazonaws.com/emails/z.jpg" style="height: 32px;margin: 2px" />
                                <img class="bselect" src="https://wpdmcdn.s3.amazonaws.com/emails/oilpaint.jpg" style="height: 32px;margin: 2px" />
                            </div>
                        </div>
                        <div class="form-group">
                            <?php _e( "Footer Text" , "download-manager" ); ?>
                            <textarea name="__wpdm_email_setting[footer_text]" class="form-control"><?php echo isset($__wpdm_email_setting['footer_text'])?stripslashes($__wpdm_email_setting['footer_text']):'';?></textarea>
                        </div>
                        <div class="form-group">
                            <?php _e( "Facebook Page URL" , "download-manager" ); ?>
                            <input type="text" name="__wpdm_email_setting[facebook]" value="<?php echo isset($__wpdm_email_setting['facebook'])?($__wpdm_email_setting['facebook']):'';?>" class="form-control">
                        </div>
                        <div class="form-group">
                            <?php _e( "Twitter Profile URL" , "download-manager" ); ?>
                            <input type="text" name="__wpdm_email_setting[twitter]" value="<?php echo isset($__wpdm_email_setting['twitter'])?$__wpdm_email_setting['twitter']:'';?>" class="form-control">
                        </div>
                        <div class="form-group">
                            <?php _e( "Youtube Profile URL" , "download-manager" ); ?>
                            <input type="text" name="__wpdm_email_setting[youtube]" value="<?php echo isset($__wpdm_email_setting['youtube'])?$__wpdm_email_setting['youtube']:'';?>" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="w3econtainer">
                            <div class="w3erow">
                                <div class="w3ecolumn w3eleft">
                                    <span class="w3edot" style="background:#ED594A;"></span>
                                    <span class="w3edot" style="background:#FDD800;"></span>
                                    <span class="w3edot" style="background:#5AC05A;"></span>
                                </div>
                                <div class="w3ecolumn w3emiddle">
                                    Email Preview
                                </div>
                                <div class="w3ecolumn w3eright">
                                    <div style="float:right">
                                        <span class="w3ebar"></span>
                                        <span class="w3ebar"></span>
                                        <span class="w3ebar"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="w3econtent">
                                <iframe style="margin: 0;width: 100%;height: 550px;border-radius: 3px" id="preview" src="edit.php?action=email_template_preview&id=user-signup&etmpl=<?php echo $__wpdm_email_template; ?>">

                                </iframe>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="panel-footer text-right">
                <button class="btn btn-primary" id="emsbtn" style="width: 180px;"><i class="fas fa-hdd"></i> <?php _e( "Save Changes" , "download-manager" ); ?></button>
            </div>
        </div>
    </form>
    <?php } ?>

    </div>
    </div>


    <div class="modal fade" id="preview-modal" tabindex="-1" role="dialog" aria-labelledby="preview" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel"><?php _e( "Template Preview" , "download-manager" ); ?></h4>
                </div>
                <div class="modal-body" id="preview-area">

                </div>
                <div class="modal-footer text-left" style="text-align: left">
                    <div class='alert alert-info'><?php _e( "This is a preview, original template color scheme may look little different, but structure will be same" , "download-manager" ); ?></div>
                </div>
            </div>
        </div>
    </div>


    <style>
        div.notice, .updated{ display: none; }
        .xbubble
        {
            position: relative;
            padding: 10px;
            background: #eeeeee;
            -webkit-border-radius: 3px;
            -moz-border-radius: 3px;
            border-radius: 3px;
        }

        .xbubble:after
        {
            content: '';
            position: absolute;
            border-style: solid;
            border-width: 0 10px 10px;
            border-color: #eeeeee transparent;
            display: block;
            width: 0;
            z-index: 1;
            top: -10px;
            left: 17px;
        }
        .w3ebselect{
            padding: 2px;
            border-radius: 2px;
            background: #ffffff;
            cursor: pointer;
        }

        .w3econtainer {
            border: 3px solid #333;
            border-radius: 4px;
            background: #333;
        }

        /* Container for columns and the top "toolbar" */
        .w3erow {
            padding: 10px;
            background: #333;
            color: #777777;
            letter-spacing: 0.5px;
            font-size: 11px;
            font-weight: 600;
        }

        /* Create three unequal columns that floats next to each other */
        .w3ecolumn {
            float: left;
        }

        .w3eleft {
            width: 60px;
        }

        .w3eright {
            width: 10%;
        }

        .w3emiddle {
            width: calc(90% - 60px);
        }

        /* Clear floats after the columns */
        .w3erow:after {
            content: "";
            display: table;
            clear: both;
        }

        /* Three dots */
        .w3edot {
            margin-top: 4px;
            height: 12px;
            width: 12px;
            background-color: #bbb;
            border-radius: 50%;
            display: inline-block;
        }

        /* Style the input field */
        input[type=text].w3e {
            width: 100%;
            border-radius: 3px;
            border: none;
            background-color: white;
            margin-top: -8px;
            height: 25px;
            color: #666;
            padding: 5px;
        }

        /* Three bars (hamburger menu) */
        .w3ebar {
            width: 17px;
            height: 3px;
            background-color: #aaa;
            margin: 3px 0;
            display: block;
        }

        /* Page content */
        .w3econtent {
            padding: 0;
            margin-bottom: -4px;
        }

    </style>
<script>



    jQuery(function($){
        $('.bselect').click(function(){
            $('#banner-url').val(this.src);
        });
        $('.template_preview').click(function(){
            $('#preview-area').html("<i class='fa fa-spin fa-spinner'></i> Loading Preview...").load($(this).attr('data-href'));
        });
        $('#etmpl').on('change', function () {
            $('#preview').attr('src', 'edit.php?action=email_template_preview&id=user-signup&etmpl='+$(this).val());
        });
        $('#emlstform').submit(function (e) {
            e.preventDefault();
            $('#emsbtn').html('<i class="fa fa-sync fa-spin"></i> <?php _e( "Saving..." , "download-manager" ); ?>');
            $(this).ajaxSubmit({
                url: ajaxurl+"?action=wpdm_save_email_setting",
                success: function (res) {
                    $('#emsbtn').html('<i class="fas fa-hdd"></i> <?php _e( "Save Changes" , "download-manager" ); ?>');
                    document.getElementById('preview').contentDocument.location.reload(true);
                }
            });
        });

        $('.btn-status').on('click', function () {
            var v = $(this).data('value');
            var c = '.'+$(this).data('id');
            var $this = this;
            $.post(ajaxurl, {action: 'update_template_status', template: $(this).data('id'), type: '<?php echo $ttype; ?>', status: v}, function (res) {
                $(c).removeClass('btn-danger').removeClass('btn-success').addClass('btn-default');
                if(v==1)
                    $($this).addClass('btn-success').removeClass('btn-default');
                else
                    $($this).addClass('btn-danger').removeClass('btn-default');
            });


        });

        $('.delete-template').on('click', function (e) {
            if(!confirm('<?php _e( "Are you sure?" , "download-manager" ); ?>')) return false;
            e.preventDefault();
            var rowid = '#template-'+$(this).data('ttype')+"-"+$(this).data('tplid');
            $(this).html('<i class="fa fa-times fa-spin"></i> Delete');
            $.get(ajaxurl, {action: 'wpdm_delete_template', ttype: $(this).data('ttype'), tplid: $(this).data('tplid')}, function (res) {
                $(rowid).remove();
            });
        });
    });

</script>
</div>


 
