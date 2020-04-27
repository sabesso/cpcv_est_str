<?php
if (!defined('ABSPATH')) die();
/**
 * User: shahnuralam
 * Date: 1/16/18
 * Time: 12:33 AM
 */
error_reporting(0);
global $post;
$post = get_post(wpdm_query_var('__wpdmlo'));
setup_postdata($post);
$pack = new \WPDM\Package();
$pack->Prepare(get_the_ID());

?><!DOCTYPE html>
<html style="background: transparent">
<head>
    <title>Download <?php the_title(); ?></title>
    <?php if((isset($pack->PackageData['form_lock']) && $pack->PackageData['form_lock'] == 1)  || $pack->PackageData['base_price'] > 0) wp_head(); else { ?>
        <link rel="stylesheet" href="<?php echo WPDM_BASE_URL; ?>assets/bootstrap/css/bootstrap.css" />
        <link rel="stylesheet" href="<?php echo WPDM_BASE_URL; ?>assets/css/front.css" />
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" />
        <script src="<?php echo includes_url(); ?>/js/jquery/jquery.js"></script>
        <script src="<?php echo includes_url(); ?>/js/jquery/jquery.form.min.js"></script>
        <script src="<?php echo WPDM_BASE_URL; ?>assets/bootstrap/js/bootstrap.min.js"></script>
        <script src="<?php echo WPDM_BASE_URL; ?>assets/js/front.js"></script>

    <?php }

    \WPDM\libs\Apply::googleFont();
    ?>
    <style>
        html, body{
            overflow: visible;
            height: 100%;
            width: 100%;
            padding: 0;
            margin: 0;
            font-weight: 300;
            font-size: 10pt;
            font-family: var(--wpdm-font);
        }
        h4.modal-title{
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #555555;
            font-size: 11pt;
            display: inline-block;
            font-family: var(--wpdm-font);
        }

        .w3eden label{
            font-weight: 400;
        }
        img{
            max-width: 100%;
        }
        .modal-backdrop{
            background: rgba(0,0,0,0.5);
        }


        .modal.fade{
            opacity:1;
        }
        .modal.fade .modal-dialog {
            -webkit-transform: translate(0);
            -moz-transform: translate(0);
            transform: translate(0);
        }

        .modal {
            text-align: center;
            padding: 0!important;
        }

        .wpdm-social-lock.btn {
            display: block;
            width: 100%;
        }

        @media (min-width: 768px) {
            .modal:before {
                content: '';
                display: inline-block;
                height: 100%;
                vertical-align: middle;
                margin-right: -4px;
            }

            .modal-dialog {
                display: inline-block;
                text-align: left;
                vertical-align: middle;
            }

            .wpdm-social-lock.btn {
                display: inline-block;
                width: 47%;
            }
        }

        @-moz-keyframes spin {
            from { -moz-transform: rotate(0deg); }
            to { -moz-transform: rotate(360deg); }
        }
        @-webkit-keyframes spin {
            from { -webkit-transform: rotate(0deg); }
            to { -webkit-transform: rotate(360deg); }
        }
        @keyframes spin {
            from {transform:rotate(0deg);}
            to {transform:rotate(360deg);}
        }
        .spin{
            -webkit-animation-name: spin;
            -webkit-animation-duration: 2000ms;
            -webkit-animation-iteration-count: infinite;
            -webkit-animation-timing-function: linear;
            -moz-animation-name: spin;
            -moz-animation-duration: 2000ms;
            -moz-animation-iteration-count: infinite;
            -moz-animation-timing-function: linear;
            -ms-animation-name: spin;
            -ms-animation-duration: 2000ms;
            -ms-animation-iteration-count: infinite;
            -ms-animation-timing-function: linear;

            animation-name: spin;
            animation-duration: 2000ms;
            animation-iteration-count: infinite;
            animation-timing-function: linear;
            display: inline-block;
        }


        .w3eden .card-default {
            border-radius: 3px;
            margin-top: 10px !important;
        }
        .w3eden .card-default:last-child{
            margin-bottom: 0 !important;
        }
        .w3eden .card-default .card-header{
            letter-spacing: 0.5px;
            font-weight: 600;
            background-color: #f6f8f9;
        }

        .w3eden .card-default .card-footer{
            background-color: #fafafa;
        }

        .btn{
            outline: none !important;
        }
        .w3eden .card{
            margin-bottom: 0;
        }
        .w3eden .modal-header{
            border: 0;
        }
        .w3eden .modal-content{
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.2);
            border: 0;
            border-radius: 6px;
            background: rgb(255,255,255);
            overflow: hidden;
            max-width: 100%;
        }
        .w3eden .modal-body{
            max-height:  calc(100vh - 210px);
            overflow-y: auto;
            padding-top: 0 !important;
        }


        .w3eden .input-group-lg .input-group-btn .btn{
            border-top-right-radius: 4px !important;
            border-bottom-right-radius: 4px !important;
        }
        .w3eden .wpforms-field-medium{
            max-width: 100% !important;
            width: 100% !important;
        }

        .w3eden .input-group.input-group-lg .input-group-btn .btn {
            font-size: 11pt !important;
        }

        .modal-icon{
            padding: 4px;
            display: inline-block;
            width: 72px;
            height: 72px;
            border-radius: 500px;
            margin-top: -36px;
            left: calc(50% - 36px);
            box-shadow: 0 0 3px rgba(0,0,0,0.3);
            position: absolute;
            z-index: 999999;
            background: rgb(254,254,254);
            background: -moz-linear-gradient(45deg,  rgba(254,254,254,1) 19%, rgba(226,226,226,1) 100%);
            background: -webkit-linear-gradient(45deg,  rgba(254,254,254,1) 19%,rgba(226,226,226,1) 100%);
            background: linear-gradient(45deg,  rgba(254,254,254,1) 19%,rgba(226,226,226,1) 100%);
            filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#fefefe', endColorstr='#e2e2e2',GradientType=1 );
        }

        .modal-content{
            padding-top: 36px !important;
        }
        .close{
            position: absolute;
            z-index: 999999;
            top: 5px;
            right: 5px;
            opacity: 0 !important;
        }
        .modal-content:hover .close{
            opacity: 0.8 !important;
        }
        .close:hover .fa-times-circle{
            color: #ff3c54 !important;
        }
        .close .fa-times-circle,
        .close:hover .fa-times-circle,
        .modal-content:hover .close,
        .close{
            -webkit-transition: ease-in-out 400ms;
            -moz-transition: ease-in-out 400ms;
            -ms-transition: ease-in-out 400ms;
            -o-transition: ease-in-out 400ms;
            transition: ease-in-out 400ms;
        }
        .wp-post-image{
            width: 100%;
            height: auto;
            border-radius: 500px;
        }

        .btn-viewcart,
        #cart_submit{
            line-height: 30px !important;
            width: 100%;
        }
        .w3eden h3.wpdmpp-product-price{
            text-align: center;
            margin-bottom: 30px !important;
        }
        .modal-icon img{
            border-radius: 500px;
        }
    </style>




    <?php do_action("wpdm_modal_iframe_head"); ?>
</head>
<body class="w3eden" style="background: transparent">

<div class="modal fade" id="wpdm-locks" tabindex="-1" role="dialog" aria-labelledby="wpdm-optinmagicLabel">
    <div class="modal-dialog" role="document" style="width: <?php echo isset($pack->PackageData['terms_lock']) && $pack->PackageData['terms_lock'] == 1?395:365; ?>px;max-width: calc(100% - 20px);">
        <div class="modal-icon">
            <?php the_post_thumbnail('thumbnail'); ?>
            <?php //echo $pack->PackageData['icon']; ?>
        </div>
        <div class="modal-content">
            <div class="text-center mt-3 mb-3">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="far fa-times-circle"></i></span></button>
                <h4 class="d-block"><?php echo ($pack->PackageData['base_price'] > 0)? __('Buy','download-manager'): __('Download','download-manager'); ?></h4>
                <div style="letter-spacing: 1px;font-weight: 400;margin-top: 5px" class="color-purple d-block"><?php echo $pack->PackageData['title']; ?></div>
            </div>
            <div class="modal-body">
                <?php
                echo $pack->PackageData['download_link_extended'];
                ?>
            </div>

        </div>

    </div>
    <?php

    ?>
</div>

<script>

    jQuery(function ($) {

        $('a').each(function () {
            $(this).attr('target', '_blank');
        });

        $('body').on('click','a', function () {
            $(this).attr('target', '_blank');
        });

        $('#wpdm-locks').on('hidden.bs.modal', function (e) {
            var parentWindow = document.createElement("a");
            parentWindow.href = document.referrer.toString();
            if(parentWindow.hostname === window.location.hostname)
                window.parent.hideLockFrame();
            else
                window.parent.postMessage({'task': 'hideiframe'}, "*");
        });


    });

    function showModal() {
        jQuery('#wpdm-locks').modal('show');
    }
    showModal();
</script>
<div style="display: none">
    <?php  if((isset($pack->PackageData['form_lock']) && $pack->PackageData['form_lock'] == 1)  || $pack->PackageData['base_price'] > 0) wp_footer(); ?>
    <?php do_action("wpdm_modal_iframe_footer"); ?>
</div>
</body>
</html>
