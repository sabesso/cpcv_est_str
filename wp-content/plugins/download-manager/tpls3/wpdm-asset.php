<?php
/**
 * Base: wpdmpro
 * Developer: shahjada
 * Team: W3 Eden
 * Date: 2019-08-08 12:34
 * Date: 2019-08-17
 * Version: 1.1.0
 */
if(!defined("ABSPATH")) die();
?>
<div class="w3eden">
    <div class="panel panel-default card card-default wpdm-asset-link wpdm-asset-link-<?php echo $asset->ID; ?>">
        <div class="card-body panel-body">
            <div style="display: flex">
                <div style="width: 100% !important;display: grid"><h3 class="package-title" style="font-size: 12pt;margin: 0;line-height: 18px;margin-bottom: 2px"><?php echo $asset->name; ?></h3>
                    <small class="text-muted"><?php echo $asset->size; ?></small>
                </div>
                <div>
                    <?php if($asset->access == 'public' || is_user_logged_in()) { ?>
                        <a class="btn btn-primary btn-lg" href="<?php echo $asset->temp_download_url; ?>"><?php echo __( "Download", "download-manager" ); ?></a>
                    <?php } else { ?>
                        <a class="btn btn-danger btn-lg" href="<?php echo wpdm_login_url($_SERVER['REQUEST_URI']); ?>"><?php echo __( "Login to Download", "download-manager" ); ?></a>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>