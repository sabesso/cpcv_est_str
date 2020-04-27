<?php
$id = esc_attr($_GET['id']);
$tpl = \WPDM\Email::template($id);
$info = \WPDM\Email::info($id);

?><div class="wrap w3eden">
     <div class="panel panel-default" id="wpdm-wrapper-panel">
         <div class="panel-heading">
             <b><i class="fa fa-magic color-purple"></i> &nbsp; <?php echo __( "Templates" , "download-manager" ); ?></b>

             <div style="clear: both"></div>
         </div>
         <ul id="tabs" class="nav nav-tabs nav-wrapper-tabs" style="padding: 60px 10px 0 10px;background: #f5f5f5">
             <li><a href="edit.php?post_type=wpdmpro&page=templates&_type=link" id="basic"><?php echo __( "Link Templates" , "download-manager" ); ?></a></li>
             <li><a href="edit.php?post_type=wpdmpro&page=templates&_type=page" id="basic"><?php echo __( "Page Templates" , "download-manager" ); ?></a></li>
             <li><a href="edit.php?post_type=wpdmpro&page=templates&_type=email" id="basic"><?php _e( "Email Templates" , "download-manager" ); ?></a></li>
             <li class="active"><a href="" id="basic"><?php echo __( "Email Template Editor" , "download-manager" ); ?></a></li>

         </ul>
         <div class="tab-content" style="padding-top: 15px;">


             <?php if(isset($tpl['subject'])){ ?>
<div style="padding: 15px;">
<div class="row">
<div class="col-md-12">
    <div class="well" style="font-size: 11pt;font-weight: 600">
        <div class="pull-right">
            <?php echo sprintf(__('To: %s'), ucfirst($info['for'])); ?>
        </div>
        <?php echo sprintf(__('Editing: %s'), $info['label']); ?>
    </div>
    </div>
    </div>
    <div class="row">
<div class="col-md-8">

<form action="" method="post" id="email-editor-form">
    <?php wp_nonce_field(NONCE_KEY, '__wpdm_nonce') ?>

                <input type="hidden" name="id" value="<?php echo $id; ?>" />
                <input type="text" name="email_template[subject]" required="required" title="<?php echo __( "Email Subject" , "download-manager" ); ?>" placeholder="<?php echo __( "Email Subject" , "download-manager" ); ?>" x-moz-errormessage="<?php echo __( "Email Subject" , "download-manager" ); ?>" value="<?php echo $tpl['subject']; ?>" class="form-control input-lg">
                <ul class="nav nav-tabs" style="margin-top: 10px; ">
                    <li class="active"><a href="#code" data-toggle="tab"><?php echo __( "Message" , "download-manager" ); ?></a></li>
                    <li><a href="#preview" data-toggle="tab"><?php echo __( "Preview" , "download-manager" ); ?></a></li>
                </ul>
                <div class="tab-content tpleditor">
                    <div class="tab-pane active" id="code">

                        <?php wp_editor(stripslashes(wpdm_escs($tpl['message'])),'content', array('textarea_name' => 'email_template[message]')); ?>
                    </div>
                    <div class="tab-pane" id="preview">
                        <i class="fas fa-sun  fa-spin"></i> Loading Preview...
                    </div>
                </div>
    <br/>
    <?php
    if($info['for'] == 'admin'){ ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <div class="input-group input-group-lg">
                                <div class="input-group-addon ttip" title="<?php echo __( "Recipient's Email" , "download-manager" ); ?>"><i class="fas fa-paper-plane"></i></div>
                                <input placeholder="<?php echo __( "Recipient's Email" , "download-manager" ); ?>" type="text" class="form-control input-lg" name="email_template[to_email]" value="<?php echo isset($tpl['to_email'])?$tpl['to_email']:""; ?>">
                            </div>

                        </div>
                    </div>

                </div>
                <?php } ?>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <?php echo __( "From Name" , "download-manager" ); ?>
                                <input type="text" class="form-control" name="email_template[from_name]" value="<?php echo $tpl['from_name']; ?>">

                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <?php echo __( "From Email" , "download-manager" ); ?>
                                <input type="text" class="form-control" name="email_template[from_email]" value="<?php echo sanitize_email($tpl['from_email']); ?>">

                        </div>
                    </div>
                </div>




<br/>
    <div class="text-right"><button type="submit" value="" class="btn btn-primary btn-lg"><i class="fas fa-hdd"></i> <?php echo __( "Save Changes" , "download-manager" ); ?></button></div>




				<br/>




</form>



</div>









<div class="col-md-4">
    <div class="panel panel-default">
        <div class="panel-heading"><?php echo __( "Template Variables" , "download-manager" ); ?></div>

           <table id="template_tags" class="table" style="margin-top: -1px">
           <?php foreach (\WPDM\Email::tags() as $tag => $info){ ?>
           <tr><td><input type="text" readonly="readonly" class="form-control"  onclick="this.select()" value="<?php echo $tag; ?>" style="font-size:10px;width: 120px;text-align: center;"></td><td><?php echo $info['desc']; ?></td></tr>
           <?php } ?>

           </table>


        </div>
</div>
</div>
<script>

    jQuery.fn.extend({
        insertAtCaret: function(myValue){
            return this.each(function(i) {
                if (document.selection) {
                    //For browsers like Internet Explorer
                    this.focus();
                    var sel = document.selection.createRange();
                    sel.text = myValue;
                    this.focus();
                }
                else if (this.selectionStart || this.selectionStart == '0') {
                    //For browsers like Firefox and Webkit based
                    var startPos = this.selectionStart;
                    var endPos = this.selectionEnd;
                    var scrollTop = this.scrollTop;
                    this.value = this.value.substring(0, startPos)+myValue+this.value.substring(endPos,this.value.length);
                    this.focus();
                    this.selectionStart = startPos + myValue.length;
                    this.selectionEnd = startPos + myValue.length;
                    this.scrollTop = scrollTop;
                } else {
                    this.value += myValue;
                    this.focus();
                }
            });
        }
    });

    jQuery(function(){
        jQuery('a[href="#preview"]').on('shown.bs.tab', function (e) {
            jQuery('#preview').html('<i class="fas fa-sun  fa-spin"></i> Loading Preview...');
            tinyMCE.triggerSave();
            jQuery('#email-editor-form').ajaxSubmit({
                success: function (res) {
                    jQuery('#preview').html("<iframe style='width:100%;height:700px;border:0;' src='edit.php?action=email_template_preview&id=<?php echo esc_attr($_GET['id']); ?>'></iframe>");
                }
            });
            //jQuery.post(ajaxurl,{action:'',template:'<?php echo wpdm_query_var('id'); ?>'},function(res){

            //});
        });

        jQuery('.dropdown-menu a').click(function(e){
            e.preventDefault();
            var tag = jQuery(this).attr('href').replace('#','');
            jQuery('#content').insertAtCaret(tag);
        });

        jQuery('#template_tags .form-control').on('select', function(){
            try {
                var successful = document.execCommand('copy');
                var msg = successful ? 'successful' : 'unsuccessful';
                console.log('Copying text command was ' + msg);
            } catch (err) {
                console.log('Oops, unable to copy');
            }
        })
    });

</script>



<div style="clear: both"></div>


</div>
             <?php } else { ?>
                <div class="alert alert-danger" style="margin: 30px">
                    <?php echo __( "Invalid template ID!", "download-manager" ) ?>
                </div>
             <?php } ?>
</div>
</div>
</div>


<style>
    #template_tags .form-control{
        background: #fafafa;
    }
    #wp-content-editor-tools{
        background: #ffffff;
    }
    .wp-editor-tabs{
        margin-top: 3px;
    }
    .input-group-lg .input-group-addon{
        border-radius: 3px 0 0 3px !important;
    }
    .input-group-lg .form-control{
        border-radius: 0 3px 3px 0 !important;
    }
    .updated{
        display: none !important;
    }
</style>