<?php
/*
Asset Manager for WordPress Download Manager
Author: Shahjada
Version: 1.0.0
*/

namespace WPDM;

use WPDM\libs\Crypt;

if(!class_exists('AssetManager')):
    class AssetManager{
        private static $instance;
        private $dir,$url, $root;
        private $mime_type;

        public static function getInstance(){
            if(self::$instance === null){
                self::$instance = new self;
                self::$instance->dir = dirname(__FILE__);
                self::$instance->url = WP_PLUGIN_URL . '/' . basename(self::$instance->dir);
                self::$instance->actions();
                //print_r($_SESSION);
            }
            return self::$instance;
        }

        public static function root($path = ''){
            global $current_user;
            $root = current_user_can('manage_options')?rtrim(get_option('_wpdm_file_browser_root'), '/').'/' : UPLOAD_DIR . $current_user->user_login . '/';
            $_root = str_replace("\\", "/", $root);
            if($path !== '') $root .= $path;
            $root = realpath($root);
            $root = str_replace("\\", "/", $root);
            if($path!== '' && !strstr($root, $_root)) return null;
            if(is_dir($root)) $root .= "/";
            if($path === '' && !file_exists($root)){
                @mkdir($root, 0775, true);
                \WPDM\libs\FileSystem::blockHTTPAccess($root);
            }
            return $root;
        }

        private function actions(){

            add_action('init',array($this,'download'),1);

            add_action('init',array($this,'assetPicker'),1);

            //add_action('wp_ajax_wpdm_fm_file_upload', array($this,'uploadFile'));
            add_action('wp_ajax_wpdm_mkdir', array($this,'mkDir'));
            add_action('wp_ajax_wpdm_newfile', array($this,'newFile'));
            add_action('wp_ajax_wpdm_scandir', array($this,'scanDir'));
            add_action('wp_ajax_wpdm_openfile', array($this,'openFile'));
            add_action('wp_ajax_wpdm_filesettings', array($this,'fileSettings'));
            add_action('wp_ajax_wpdm_unlink', array($this,'deleteItem'));
            add_action('wp_ajax_wpdm_rename', array($this,'renameItem'));
            add_action('wp_ajax_wpdm_savefile', array($this,'saveFile'));
            add_action('wp_ajax_wpdm_copypaste', array($this,'copyItem'));
            add_action('wp_ajax_wpdm_cutpaste', array($this,'moveItem'));

            add_action('wpdm_after_upload_file', array($this,'upload'));

            //add_action('wp_enqueue_scripts', array($this,'siteScripts'));
            add_action('admin_enqueue_scripts', array($this,'adminScripts'), 999);

            //add_shortcode('wpdm_asset_manager', array($this,'_assetManager'));
            add_shortcode('wpdm_asset', array($this,'wpdmAsset'));

            //add_filter('wpdm_frontend', array($this,'frontendFileManagerTab'));

            if(is_admin()){
                add_action('admin_menu',array($this,'adminMenu'),10);
            }

        }

        function siteScripts(){
            global $post;

            if(is_single() && !has_shortcode($post->post_content, '[wpdm_asset_manager]')) return;

            $cm_settings['codeEditor'] = wp_enqueue_code_editor(array('type' => 'text/plain'));
            wp_localize_script('jquery', 'wpdmcm_settings', $cm_settings);

            wp_enqueue_script('wp-theme-plugin-editor');
            wp_enqueue_style('wp-codemirror');

            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-ui-autocomplete');
        }


        function adminScripts($hook){
            if($hook !== 'wpdmpro_page_wpdm-asset-manager') return;

            $cm_settings['codeEditor'] = wp_enqueue_code_editor(array('type' => 'text/plain'));
            wp_localize_script('jquery', 'wpdmcm_settings', $cm_settings);

            wp_enqueue_script('wp-theme-plugin-editor');
            wp_enqueue_style('wp-codemirror');

            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-ui-autocomplete');

        }

        function assetPicker(){
            global $wp_query;
            if(wpdm_query_var('assetpicker', 'int') === 1) {
                http_response_code(200);
                include WPDM_BASE_DIR.'admin/tpls/asset-manager-picker.php';;
                die();
            }
        }

        public function download(){
            if(isset($_REQUEST['asset']) && isset($_REQUEST['key'])){
                $asset = new Asset();
                $asset->get(wpdm_query_var('asset', 'int'));
                if(wp_verify_nonce($_REQUEST['key'], $asset->path))
                    $asset->download();
                else
                    \WPDM_Messages::error(apply_filters('wpdm_asset_download_link_expired_message', __( "Download link is expired! Go back and Refresh the page to regenerate download link", "download-manager" )), 1);
                die();
            }
            if(isset($_REQUEST['wpdmfmdl']) && is_user_logged_in()){
                global $current_user;
                $file = AssetManager::root(Crypt::decrypt(wpdm_query_var('wpdmfmdl')));
                if(!$file) \WPDM_Messages::error("File Not Found!", 1);
                \WPDM\libs\FileSystem::downloadFile($file, wp_basename($file));
                die();
            }
        }


        public static function getDir(){
            return self::$instance->dir;
        }

        public static function getUrl(){
            return self::$instance->url;
        }

        public function adminMenu(){
            add_submenu_page( 'edit.php?post_type=wpdmpro',__("Asset Manager", 'download-manager'), __('Asset Manager','download-manager'), 'access_server_browser', 'wpdm-asset-manager', array($this,'_assetManager'));
        }

        function mkDir(){
            global $current_user;
            if(isset($_REQUEST['__wpdm_mkdir']) && !wp_verify_nonce($_REQUEST['__wpdm_mkdir'], NONCE_KEY)) wp_send_json(array('success' => false, 'message' => __( "Error! Session Expired. Try refreshing page.", "download-manager" )));
            check_ajax_referer(NONCE_KEY, '__wpdm_mkdir');
            if(!current_user_can(WPDM_ADMIN_CAP)) wp_send_json(array('success' => false, 'message' => __( "Error! Unauthorized Access.", "download-manager" )));
            $root = AssetManager::root();
            $relpath = Crypt::decrypt(wpdm_query_var('path'));
            $path =  AssetManager::root($relpath);
            if(!$path) wp_send_json(array('success' => false, 'message' => __( "Error! Unauthorized Path.", "download-manager" )));
            $name = wpdm_query_var('name', 'filename');
            mkdir($path.$name);
            wp_send_json(array('success' => true, 'path' => $path.$name));
        }

        function newFile(){
            global $current_user;
            if(isset($_REQUEST['__wpdm_newfile']) && !wp_verify_nonce($_REQUEST['__wpdm_newfile'], NONCE_KEY)) wp_send_json(array('success' => false, 'message' => __( "Error! Session Expired. Try refreshing page.", "download-manager" )));
            check_ajax_referer(NONCE_KEY, '__wpdm_newfile');
            if(!current_user_can(WPDM_ADMIN_CAP))  wp_send_json(array('success' => false, 'message' => __( "Error! Unauthorized Access.", "download-manager" )));
            $relpath = Crypt::decrypt(wpdm_query_var('path'));
            $path =  AssetManager::root($relpath);
            if(!$path) wp_send_json(array('success' => false, 'message' => __( "Error! Unauthorized Path.", "download-manager" )));
            $name = wpdm_query_var('name');
            $ret = file_put_contents($path.$name, '');
            if($ret !== false)
                wp_send_json(array('success' => true, 'filepath' => $path.$name));
            else
                wp_send_json(array('success' => false, 'filepath' => $path.$name));

        }

        function scanDir(){
            if(isset($_REQUEST['__wpdm_scandir']) && !wp_verify_nonce($_REQUEST['__wpdm_scandir'], NONCE_KEY)) wp_send_json(array('success' => false, 'message' => __( "Error! Session Expired. Try refreshing page.", "download-manager" )));
            check_ajax_referer(NONCE_KEY, '__wpdm_scandir');
            if(!current_user_can(WPDM_ADMIN_CAP)) wp_send_json(array('success' => false, 'message' => __( "Error! Session Expired. Try refreshing page.", "download-manager" )));
            global $current_user;
            $root = AssetManager::root();
            $relpath = Crypt::decrypt(wpdm_query_var('path'));
            $path =  AssetManager::root($relpath);
            if(!$path) wp_send_json(array('success' => false, 'message' => __( "Error! Unauthorized Path.", "download-manager" )));
            $items = scandir($path, SCANDIR_SORT_ASCENDING );
            $items = array_diff($items, array('.','..'));
            $_items = array();
            $_dirs = array();
            Session::set('working_dir', $path);
            foreach ($items as $item){

                $item_label = $item;
                $item_label = esc_attr($item_label);
                //$item_label = strlen($item_label) > 30 ? substr($item_label, 0, 15) . "..." . substr($item_label, strlen($item_label) - 15) : $item_label;
                $ext = explode('.', $item); $ext = end($ext);
                $icon = file_exists(WPDM_BASE_DIR.'/assets/file-type-icons/'.$ext.'.svg')?plugins_url('download-manager/assets/file-type-icons/'.$ext.'.svg'):plugins_url('download-manager/assets/file-type-icons/unknown.svg');
                $type = is_dir($path.$item)?'dir':'file';
                $note = is_dir($path.$item)?(count(scandir($path.$item))-2).' items':number_format((filesize($path.$item)/1024),2).' KB';
                $rpath = str_replace($root, "", $path.$item);
                $wp_rel_path = str_replace(UPLOAD_DIR, "", $path.$item);
                $wp_rel_path = str_replace(ABSPATH, "", $wp_rel_path);
                $_rpath = Crypt::encrypt($rpath);
                if($type === 'dir') {
                    $_dirs[] = array('item_label' => $item_label, 'item' => $item, 'icon' => $icon, 'type' => $type, 'note' => $note, 'path' => $_rpath, 'id' => md5($rpath));
                }
                else {
                    $contenttype = function_exists('mime_content_type') ? mime_content_type($path.$item) : self::mime_type($path.$item);
                    $_items[] = array('item_label' => $item_label, 'item' => $item, 'icon' => $icon, 'type' => $type, 'contenttype' => $contenttype, 'note' => $note, 'path' => $_rpath, 'path_on' => $path.$item, 'wp_rel_path' => $wp_rel_path, 'id' => md5($rpath));
                }

            }

            $allitems = $_dirs;
            foreach ($_items as $_item){
                $allitems[] = $_item;
            }
            $parts = explode("/", $relpath);
            $breadcrumb[] = "<a href='#' class='media-folder' data-path=''>".__( "Home", "download-manager" )."</a>";
            $topath = array();
            foreach ($parts as $part){
                $topath[] = $part;
                $rpath = Crypt::encrypt(implode("/", $topath));
                $breadcrumb[] = "<a href='#' class='media-folder' data-path='{$rpath}'>".esc_attr($part)."</a>";
            }
            $breadcrumb = implode("<i class='fa fa-folder-open'></i>", $breadcrumb);
            if((int)wpdm_query_var('dirs') === 1)
                wp_send_json($_dirs);
            else
                wp_send_json(array('success' => true, 'items' => $allitems, 'breadcrumb' => $breadcrumb));
            die();
        }

        static function mime_type($file){
            $contenttype = wp_check_filetype($file);
            $contenttype = $contenttype['type'];
            if(!$contenttype){
                $file = explode(".", $file);
                $contenttype = "unknown/".end($file);
            }
            return $contenttype;
        }

        function deleteItem(){

            if(isset($_REQUEST['__wpdm_unlink']) && !wp_verify_nonce($_REQUEST['__wpdm_unlink'], NONCE_KEY)) wp_send_json(array('success' => false, 'message' => __( "Error! Session Expired. Try refreshing page.", "download-manager" )));
            check_ajax_referer(NONCE_KEY, '__wpdm_unlink');
            if(!current_user_can(WPDM_ADMIN_CAP)) wp_send_json(array('success' => false, 'message' => __( "Error! Session Expired. Try refreshing page.", "download-manager" )));

            $relpath = Crypt::decrypt(wpdm_query_var('delete'));
            $path =  AssetManager::root($relpath);
            if(!$path) wp_send_json(array('success' => false, 'message' => __( "Error! Unauthorized Path.", "download-manager" )));
            if(is_dir($path))
                $this->rmDir($path);
            else
                unlink($path);

            Asset::delete($path);

            die($path);
        }

        function openFile(){
            if(isset($_REQUEST['__wpdm_openfile']) && !wp_verify_nonce($_REQUEST['__wpdm_openfile'], NONCE_KEY)) wp_send_json(array('success' => false, 'message' => __( "Error! Session Expired. Try refreshing page.", "download-manager" )));
            check_ajax_referer(NONCE_KEY, '__wpdm_openfile');
            if(!current_user_can(WPDM_ADMIN_CAP)) wp_send_json(array('success' => false, 'message' => __( "Error! Session Expired. Try refreshing page.", "download-manager" )));
            $relpath = Crypt::decrypt(wpdm_query_var('file'));
            $path =  AssetManager::root($relpath);
            if(!$path) wp_send_json(array('success' => false, 'message' => __( "Error! Unauthorized Path.", "download-manager" )));
            if(file_exists($path) && is_file($path)){
                $cid = uniqid();
                \WPDM\Session::set($cid, $path);
                $type = mime_content_type($path);
                if(strstr("__{$type}", "text/"))
                    wp_send_json(array('content' => file_get_contents($path), 'id' => $cid));
                else if(strstr("__{$type}", "svg"))
                    wp_send_json(array('content' => '', 'embed' => file_get_contents($path), 'id' => $cid));
                else {
                    $fetchurl = home_url("/?wpdmfmdl=".wpdm_query_var('file'));
                    if (strstr("__{$type}", "image/")) {
                        $embed_code = "<img src='$fetchurl' />";
                        wp_send_json(array('content' => '', 'embed' => $embed_code, 'id' => $cid));
                    }
                    if (strstr("__{$type}", "audio/")) {
                        $embed_code = do_shortcode("[audio src='$fetchurl']");
                        wp_send_json(array('content' => '', 'embed' => $embed_code, 'id' => $cid));
                    }
                    if (strstr("__{$type}", "video/")) {
                        $embed_code = do_shortcode("[video src='$fetchurl']");
                        wp_send_json(array('content' => '', 'embed' => $embed_code, 'id' => $cid));
                    }
                }



            } else {
                \WPDM_Messages::error("Couldn't open file ( $path )!", 0);
                die();
            }

        }

        function fileSettings(){

            if(isset($_REQUEST['__wpdm_filesettings']) && !wp_verify_nonce($_REQUEST['__wpdm_filesettings'], NONCE_KEY)) wp_send_json(array('success' => false, 'message' => __( "Error! Session Expired. Try refreshing page.", "download-manager" )));
            check_ajax_referer(NONCE_KEY, '__wpdm_filesettings');
            if(!current_user_can(WPDM_ADMIN_CAP)) wp_send_json(array('success' => false, 'message' => __( "Error! Unauthorized Access.", "download-manager" )));
            $relpath = Crypt::decrypt(wpdm_query_var('file'));
            $path =  AssetManager::root($relpath);
            if(!$path) wp_send_json(array('success' => false, 'message' => __( "Error! Unauthorized Path.", "download-manager" )));
            if(file_exists($path)){
                $asset = new \WPDM\Asset($path);
                wp_send_json($asset);
            } else {
                wp_send_json(array('success' => false, 'message' => __( "Error! Unauthorized Path.", "download-manager" )));
                die();
            }

        }

        function saveFile(){
            if(isset($_REQUEST['__wpdm_savefile']) && !wp_verify_nonce($_REQUEST['__wpdm_savefile'], NONCE_KEY)) wp_send_json(array('success' => false, 'message' => __( "Error! Session Expired. Try refreshing page.", "download-manager" )));
            check_ajax_referer(NONCE_KEY, '__wpdm_savefile');
            if(!current_user_can(WPDM_ADMIN_CAP)) wp_send_json(array('success' => false, 'message' => __( "Error! Session Expired. Try refreshing page.", "download-manager" )));

            $ofilepath = \WPDM\Session::get(wpdm_query_var('opened'));
            $relpath = Crypt::decrypt(wpdm_query_var('file'));
            $path =  AssetManager::root($relpath);
            if(!$path) wp_send_json(array('success' => false, 'message' => __( "Error! Unauthorized Path.", "download-manager" )));
            if(file_exists($path) && is_file($path)){
                $content = stripslashes_deep($_POST['content']);
                file_put_contents($path, $content);
                wp_send_json(array('success' => true, 'message' => 'Saved Successfully.', 'type' => 'success'));
            } else {
                wp_send_json(array('success' => false, 'message' => __( "Error! Couldn't open file ( $path ).", "download-manager" )));
            }

        }

        function renameItem(){
            if(isset($_REQUEST['__wpdm_rename']) && !wp_verify_nonce($_REQUEST['__wpdm_rename'], NONCE_KEY)) wp_send_json(array('success' => false, 'message' => __( "Error! Session Expired. Try refreshing page.", "download-manager" )));
            check_ajax_referer(NONCE_KEY, '__wpdm_rename');
            if(!current_user_can(WPDM_ADMIN_CAP)) wp_send_json(array('success' => false, 'message' => __( "Error! Session Expired. Try refreshing page.", "download-manager" )));
            global $current_user;
            $asset = new Asset();
            $asset->get(wpdm_query_var('assetid', 'int'));
            $root = AssetManager::root();
            $oldpath = $asset->path;
            $newpath = dirname($asset->path) . '/' . str_replace(array("/","\\", "\"", "'"), "_", wpdm_query_var('newname'));
            if(!strstr($newpath, $root)) die('Error!'.$newpath." -- ".$root );
            rename($oldpath, $newpath);
            $asset->updatePath($newpath);
            wp_send_json($asset);
        }

        function moveItem(){
            if(isset($_REQUEST['__wpdm_cutpaste']) && !wp_verify_nonce($_REQUEST['__wpdm_cutpaste'], NONCE_KEY)) wp_send_json(array('success'=> false, 'message' => __( "Error! Session Expired. Try refreshing page.", "download-manager" )));
            check_ajax_referer(NONCE_KEY, '__wpdm_cutpaste');
            if(!current_user_can(WPDM_ADMIN_CAP)) wp_send_json(array('success' => false, 'message' => __( "Error! Session Expired. Try refreshing page.", "download-manager" )));

            $opath = explode("|||", wpdm_query_var('source'));
            $olddir = Crypt::decrypt($opath[0]);
            $file = end($opath);
            $oldpath = AssetManager::root($olddir . '/' .$file );
            $newpath = AssetManager::root(Crypt::decrypt(wpdm_query_var('dest'))) . $file;
            if(!$oldpath) wp_send_json(array('success'=> false, 'message' => __( "Invalid source path", "download-manager" )));
            if(!$newpath) wp_send_json(array('success'=> false, 'message' => __( "Invalid destination path", "download-manager" )));
            rename($oldpath, $newpath);

            $asset = new Asset();
            $asset = $asset->get($oldpath);
            if($asset)
                $asset->updatePath($newpath);

            wp_send_json(array('success'=> true, 'message' => __( "File moved successfully", "download-manager" )));
        }

        function copyItem(){
            if(isset($_REQUEST['__wpdm_copypaste']) && !wp_verify_nonce($_REQUEST['__wpdm_copypaste'], NONCE_KEY)) wp_send_json(array('success'=> false, 'message' => __( "Error! Session Expired. Try refreshing page.", "download-manager" )));
            check_ajax_referer(NONCE_KEY, '__wpdm_copypaste');
            if(!current_user_can(WPDM_ADMIN_CAP)) wp_send_json(array('success' => false, 'message' => __( "Error! Session Expired. Try refreshing page.", "download-manager" )));
            global $current_user;
            $root = AssetManager::root();
            $opath = explode("|||", wpdm_query_var('source'));
            $olddir = Crypt::decrypt($opath[0]);
            $file = end($opath);
            $oldpath = AssetManager::root($olddir . '/' .$file );
            $newpath = AssetManager::root(Crypt::decrypt(wpdm_query_var('dest'))) . $file;
            if(!strstr($oldpath, $root)) wp_send_json(array('success'=> false, 'message' => __( "Invalid source path", "download-manager" )));
            if(!strstr($newpath, $root)) wp_send_json(array('success'=> false, 'message' => __( "Invalid destination path", "download-manager" )));
            copy($oldpath, $newpath);

            wp_send_json(array('success'=> true, 'message' => __( "File copied successfully", "download-manager" )));
        }

        function rmDir($dir){
            $files = array_diff(scandir($dir), array('.','..'));
            foreach ($files as $file) {
                (is_dir("$dir/$file")) ? $this->rmDir("$dir/$file") : unlink("$dir/$file");
            }
            return rmdir($dir);
        }

        function copyDir( $src , $dst ) {
            $src = realpath( $src );
            $dir = opendir( $src );

            $dst = realpath( $dst ) . '/' . basename($src);
            @mkdir( $dst );

            while(false !== ( $file = readdir($dir)) ) {
                if (( $file != '.' ) && ( $file != '..' )) {
                    if ( is_dir($src . '/' . $file) ) {
                        $this->copyDir($src . '/' . $file,$dst . '/' . $file);
                    }
                    else {
                        copy($src . '/' . $file,$dst . '/' . $file);
                    }
                }
            }
            closedir($dir);
        }

        function frontendFileManagerTab($tabs){
            $tabs['asset-manager'] = array('label' => 'Asset Manager', 'callback' => array($this, '_assetManager'), 'icon' => 'fa fa-copy');
            return $tabs;
        }

        function _assetManager(){

            include wpdm_tpl_path("asset-manager-ui.php");

        }

        function wpdmAsset($params){
            if(!isset($params['id'])) return \WPDM_Messages::Error(array('title' => 'Error 404', 'message' => __( "Asset not found!", "download-manager" )), -1);

            $path_or_id = (int)$params['id'];
            $asset = new Asset();
            $asset->get($path_or_id);

            if(!$asset)
                return \WPDM_Messages::Error(array('title' => 'Error 404', 'message' => __( "Asset not found!", "download-manager" )), -1);

            if(isset($params['title']))
                $asset->name = $params['title'];

            $asset->access = isset($params['access']) && in_array($params['access'], array('public', 'private')) ? $params['access'] : 'public';

            ob_start();
            include wpdm_tpl_path("wpdm-asset.php");
            $content = ob_get_clean();
            return $content;
        }

        function  upload($file){
            if(wp_verify_nonce($_REQUEST['__wpdmfm_upload'], NONCE_KEY)) {
                $working_dir = Session::get('working_dir');
                $root = AssetManager::root();
                if(!strstr($working_dir, $root)) wp_send_json(array('success' => false));
                if($working_dir != '') {
                    $dest = $working_dir . basename($file);
                    rename($file, $dest);
                    wp_send_json(array('success' => true, 'src' => $file, 'file' => $dest));
                }
                else
                    wp_send_json(array('success' => false));
            }
        }

        function extract(){
            $relpath = Crypt::decrypt(wpdm_query_var('zipfile'));
            $zipfile =  AssetManager::root($relpath);
            $reldest = Crypt::decrypt(wpdm_query_var('zipdest'));
            if($reldest == '') $reldest = dirname($zipfile);
            $zipdest =  AssetManager::root($reldest);
            if(!$zipfile || !stristr($zipfile, '.zip')) wp_send_json(array('success' => false, 'message' => __( "Error! Unauthorized Path.", "download-manager" )));
            if(!$zipdest) wp_send_json(array('success' => false, 'message' => __( "Error! Invalid Destination Path.", "download-manager" )));
            if(!class_exists('\ZipArchive'))  wp_send_json(array('success' => false, 'message' => __('Please activate "zlib" in your server to perform zip operations','download-manager')));
            $zip = new \ZipArchive();
            if ($zip->open($zipfile) === TRUE) {
                $zip->extractTo($zipdest);
                $zip->close();
                wp_send_json(array('success' => true, 'message' => __( "Unzipped successfully.", "download-manager" )));
            } else {
                wp_send_json(array('success' => false, 'message' => __( "Error! Couldn't open the zip file.", "download-manager" )));
            }
        }
    }
if(!defined('WPDM_ASSET_MANAGER') || WPDM_ASSET_MANAGER === true) {
        AssetManager::getInstance();
}
endif;
