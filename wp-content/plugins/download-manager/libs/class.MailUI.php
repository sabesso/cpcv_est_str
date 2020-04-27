<?php
/**
 * User: shahnuralam
 * Date: 17/11/18
 * Time: 1:06 AM
 */

namespace WPDM\libs;


use WPDM\Template;

class MailUI
{



    static function panel($heading, $content, $footer = ''){
        $template = new Template();
        return $template->assign('heading', $heading)
            ->assign('content', $content)
            ->assign('footer', $footer)
            ->fetch("email-templates/ui-blocks/panel.php", WPDM_BASE_DIR);
    }

    static function table($thead, $data, $css){
        $template = new Template();
        return $template->assign('thead', $thead)
            ->assign('data', $data)
            ->assign('css', $css)
            ->fetch("email-templates/ui-blocks/table.php", WPDM_BASE_DIR);
    }

}