<?php if(!defined('ABSPATH')) die(); ?>
<style type="text/css">
    #TB_Window{
        height: 580px !important;
    }
#TB_title, #TB_ajaxWindowTitle{
    font-size: 11pt;line-height: 40px;height: 40px;font-weight: 800;letter-spacing: 1px;
}
#TB_ajaxContent{
    height: 100% !important;
    width: calc(100% - 30px) !important;
}
#TB_closeWindowButton{
    margin-top: 5px;
}
#ps{
    font-size:12pt;
    font-family: 'Courier New', monospace;
    height: 80px;
    width: 100%;
    overflow: auto;
}
.screen-reader-text{ display: none; }
    label{
        font-weight: 600;
    }
</style>
<div class="w3eden"> 
<div class="pfs panel panel-default card card-default">
<div class="panel-heading card-header"><b><?php _e( "Select Options" , "download-manager" ); ?></b></div>
    <div class="panel-body card-body">
<div class="col-md-6">
    <div class="form-group">
<b><?php _e( "Number of passwords:" , "download-manager" ); ?></b><Br/>
<input class="form-control" type="number" id='pcnt' value="">
        </div><div class="form-group">
<b><?php _e( "Number of chars for each password:" , "download-manager" ); ?></b><Br/>
<input  class="form-control" type="number" id='ncp' value="">
        </div>
</div>
<div  class="col-md-6">
<b><?php _e( "Valid Chars:" , "download-manager" ); ?></b><br />
    <label><input type="checkbox" id="ls" value="1" checked="checked"> <?php _e( "Small Letters" , "download-manager" ); ?></label><br/>
    <label><input type="checkbox" id="lc" value="1"> <?php _e( "Capital Letters" , "download-manager" ); ?></label><br/>
    <label><input type="checkbox" id="nm" value="1"> <?php _e( "Numbers" , "download-manager" ); ?></label><br/>
    <label><input type="checkbox" id="sc" value="1"> <?php _e( "Special Chars" , "download-manager" ); ?></label><br/>
</div>
    </div>
    <div class="panel-footer card-footer">
        <input type="button" id="gpsc" class="btn btn-success" value="Generate" />
    </div>
</div>

<div class="pfs panel panel-default card card-default">
<div class="panel-heading card-header"><b><?php _e( "Generated Passwords" , "download-manager" ); ?></b></div>
    <div class="panel-body card-body">
<textarea id="ps" class="form-control"></textarea>
    </div>
    <div class="panel-footer card-footer">
        <input type="button" id="pins" data-target="#<?php echo wpdm_query_var('id'); ?>" class="btn btn-primary" value="<?php _e( "Insert Password(s)" , "download-manager" ); ?>" />
    </div>
</div>

</div>