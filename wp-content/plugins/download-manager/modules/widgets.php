<?php

$widgets = scandir(WPDM_BASE_DIR.'widgets/');

foreach ($widgets as $widget){
    if(strpos($widget, ".php") && file_exists(WPDM_BASE_DIR.'widgets/'.$widget))
        include_once WPDM_BASE_DIR.'widgets/'.$widget;

}

