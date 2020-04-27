<?php

namespace WPDM;


class Asset
{
    var $ID;
    var $activities = array();
    var $comments = array();
    var $access = array();
    var $links = array();
    var $metadata = array();
    var $path;
    var $preview;
    var $name;
    var $type;
    var $size;
    var $temp_download_url;
    var $dbtable;

    function __construct($path = null){
        global $wpdb;
        $this->dbtable = "{$wpdb->prefix}ahm_assets";
        if($path)
            $this->init($path);
    }

    public function init($path){
        global $wpdb;
        if(!$this->get($path)) {
            $wpdb->insert($this->dbtable, array('path' => $path));
            $this->ID = $wpdb->insert_id;
            $this->path = $path;
            $this->preview = self::preview($this->path);
            $this->name = basename($this->path);
            $this->type = is_dir($this->path)?'dir':'file';
            $this->size = wpdm_file_size($this->path);
            $this->temp_download_url = home_url("/?asset={$this->ID}&key=".wp_create_nonce($this->path));
        }
        return $this;
    }

    function get($path_or_id){
        global $wpdb;
        $id = (int)$path_or_id;
        $idcond = $id > 0 ? " or ID = '$id'":"";
        $assetmeta = $wpdb->get_row("select * from {$wpdb->prefix}ahm_assets where path = '{$path_or_id}' {$idcond}");
        if ($assetmeta){
            $this->ID = $assetmeta->ID;
            $this->activities = maybe_unserialize($assetmeta->activities);
            $this->comments = maybe_unserialize($assetmeta->comments);
            $this->access = maybe_unserialize($assetmeta->access);
            $this->links = array();
            $this->metadata = maybe_unserialize($assetmeta->metadata);
            $this->path = $assetmeta->path;
            $this->preview = self::preview($this->path);
            $this->name = basename($this->path);
            $this->type = is_dir($this->path)?'dir':'file';
            $this->size = wpdm_file_size($this->path);
            $this->temp_download_url = home_url("/?asset={$this->ID}&key=".wp_create_nonce($this->path));
            return $this;
        }
        return null;
    }

    public static function preview($path){
        global $current_user;
        $ext = explode('.', $path);
        $ext = end($ext);
        $ext = strtolower($ext);
        $url = str_replace(ABSPATH, home_url('/'), $path);
        $accessible = strstr($url, "://") ? get_headers($url) : false;
        $accessible = strstr($accessible[0], '403') ? false : true;
        $relpath = str_replace(AssetManager::root(), "", $path);
        if(!$accessible)
            $url = home_url("/?wpdmfmdl={$relpath}");
        $image = array('png', 'jpg', 'jpeg');
        if(is_dir($path))
            return "<img style='padding:20px;width: 128px' src='".WPDM_BASE_URL."assets/images/folder.svg"."' alt='Preview' />";
        if(in_array($ext, $image))
            return "<img style='padding:20px;' src='".wpdm_dynamic_thumb($path, array(400, 0), false)."' alt='Preview' />";
        if($ext == 'svg')
            return file_get_contents($path);
        if($ext == 'mp3')
            return do_shortcode("[audio src='$url']");
        if($ext == 'mp4')
            return do_shortcode("[video src='$url']");

        $icon = file_exists(WPDM_BASE_DIR."assets/file-type-icons/{$ext}.svg" ) ? WPDM_BASE_URL."assets/file-type-icons/{$ext}.svg" : WPDM_BASE_URL."assets/file-type-icons/unknown.svg";

        return "<img src='$icon' style='padding: 20px;width: 128px'/>";

    }

    public static function view($path){
        global $current_user;
        $ext = explode('.', $path);
        $ext = end($ext);
        $ext = strtolower($ext);
        $url = str_replace(ABSPATH, home_url('/'), $path);
        $accessible = get_headers($url);
        $accessible = strstr($accessible[0], '403') ? false : true;
        $relpath = str_replace(AssetManager::root(), "", $path);
        $asset = new Asset($path);
        if(!$accessible)
            $url = $asset->temp_download_url;
        $image = array('png', 'jpg', 'jpeg');

        if(is_dir($path))
            return $asset->dirViewer();

        if(in_array($ext, $image))
            return "<div  class='wpdm-asset wpdm-asset-image'><img title='Click for fullscreen view' style='cursor: pointer' onclick='this.requestFullscreen();' src='{$asset->temp_download_url}' alt='Preview' /></div>";

        if($ext == 'svg')
            return "<div class='wpdm-asset wpdm-asset-svg'>".file_get_contents($path)."</div>";

        if($ext == 'mp3')
            return do_shortcode("<div class='wpdm-asset wpdm-asset-audio'>[audio src='$url']</div>");

        if($ext == 'mp4')
            return do_shortcode("<div class='wpdm-asset wpdm-asset-video'>[video src='$url']</div>");

        if(strstr(mime_content_type($path), "text/")) {
            $class = str_replace("text/", "", mime_content_type($path));
            $script = '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.15.9/styles/default.min.css"><script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.15.9/highlight.min.js"></script><script>hljs.initHighlightingOnLoad();</script>';
            return "<pre class='wpdm-asset wpdm-asset-text'><code class='$class'>" . esc_attr(file_get_contents($path)) . "</code></pre>{$script}";
        }

        $icon = file_exists(WPDM_BASE_DIR."assets/file-type-icons/{$ext}.svg" ) ? WPDM_BASE_URL."assets/file-type-icons/{$ext}.svg" : WPDM_BASE_URL."assets/file-type-icons/unknown.svg";

        return "<img src='$icon' style='padding: 20px;width: 128px'/>";

    }

    function hasAccess(){
        global $current_user;
        $roles = isset($this->access['roles']) && is_array($this->access['roles']) ? $this->access['roles'] : array();
        $users = isset($this->access['users']) && is_array($this->access['users']) ? $this->access['users'] : array();
        if(current_user_can('manage_options')) return true;
        if(count(array_intersect($current_user->roles, $roles)) > 0) return true;
        if(in_array('guest', $roles)) return true;
        if(in_array($current_user->user_login, $users)) return true;
        return false;
    }

    function dirViewer(){
        return "";
    }

    function updatePath($new_path){
        global $wpdb;
        $this->path = $new_path;
        $this->name = basename($new_path);
        $wpdb->update($this->dbtable, array('path' => $this->path ), array('ID' => $this->ID));
        return $this;
    }

    function save(){
        global $wpdb;
        $data = array(
          'activities' => serialize($this->activities),
          'comments' => serialize($this->comments),
          'access' => serialize($this->access),
          'metadata' => serialize($this->metadata),
        );
        $wpdb->update($this->dbtable, $data, array('ID' => $this->ID));
        return $this;
    }

    public static function delete($path_or_id){
        global $wpdb;
        $path_or_id = wpdm_sanitize_var($path_or_id);
        $id = (int)$path_or_id;
        return $wpdb->query("delete from {$wpdb->prefix}ahm_assets where ID='{$id}' or path='{$path_or_id}'");
    }

    function download(){
        \WPDM\libs\FileSystem::downloadFile($this->path, wp_basename($this->name));
    }
}