<?php

namespace WPDM\libs;

use WPDM\Package;
use WPDM\Session;
use WPDM\TempStorage;

class FileSystem
{
    function __construct(){

    }

    public static function mime_type($filename){
        $filetype = wp_check_filetype($filename);
        return $filetype['type'];
    }

    public static function uploadFile($FILE){

    }

    /**
     * @usage Download Given File
     * @param $filepath
     * @param $filename
     * @param int $speed
     * @param int $resume_support
     * @param array $extras
     */
    public static function downloadFile($filepath, $filename, $speed = 1024, $resume_support = 1, $extras = array())
    {

        if (isset($extras['package']))
            $package = $extras['package'];

        if (headers_sent($_filename, $_linenum)){
            \WPDM_Messages::error("Headers already sent in $_filename on line $_linenum", 1);
        }

        if(wpdm_is_url($filepath)){
            header("location: ".$filepath);
            die();
        }

        $ext = explode('.', $filepath);
        $ext = end($ext);
        if(in_array($ext, array('php', 'js', 'html'))) \WPDM_Messages::error("Invalid File Type (.{$ext})!", 1);

        $content_type = function_exists('mime_content_type') ?  mime_content_type($filepath) : self::mime_type($filepath);

        $buffer = $speed ? $speed : 1024; // bytes

        $buffer *= 1024; // to bits

        $bandwidth = 0;

        if( function_exists('ini_set') )
            @ini_set( 'display_errors', 0 );

        @session_write_close();

        if ( function_exists( 'apache_setenv' ) )
            @apache_setenv( 'no-gzip', 1 );

        if( function_exists('ini_set') )
            @ini_set('zlib.output_compression', 'Off');


        @set_time_limit(0);
        @session_cache_limiter('none');

        if ( get_option( '__wpdm_support_output_buffer', 1 ) == 1 ) {
            $pcl = ob_get_level();
            do {
                @ob_end_clean();
                if(ob_get_level() == $pcl) break;
                $pcl = ob_get_level();
            } while ( ob_get_level() > 0 );
        }

        if (strpos($filepath, '://'))
            $filepath = wpdm_cache_remote_file($filepath, $filename);

        if (file_exists($filepath))
            $fsize = filesize($filepath);
        else
            $fsize = 0;
        $org_size = $fsize;
        //wpdmdd($fsize);
        nocache_headers();
        header( "X-Robots-Tag: noindex, nofollow", true );
        header("Robots: none");
        header ('Content-Description: File Transfer') ;

        if(strpos($_SERVER['HTTP_USER_AGENT'],"Safari") && !isset($extras['play']) && !get_option('__wpdm_open_in_browser', 0))
            $content_type = "application/octet-stream";

        header("Content-type: $content_type");

        if(!isset($extras['play'])) {
            if (get_option('__wpdm_open_in_browser', 0) || wpdm_query_var('open') == 1)
                header("Content-disposition: inline;filename=\"{$filename}\"");
            else
                header("Content-disposition: attachment;filename=\"{$filename}\"");

            header("Content-Transfer-Encoding: binary");
        }


        if( ( isset($extras['play']) && strpos($_SERVER['HTTP_USER_AGENT'],"Safari") ) || get_option('__wpdm_download_resume',1)==2 ) {
            header("Content-Length: " . $fsize);
            header("Content-disposition: attachment;filename=\"{$filename}\"");
            \WPDM\TempStorage::set("download.".wpdm_get_client_ip(), 1, 60);
            readfile($filepath);
            return;
        }

        $file = @fopen($filepath, "rb");

        //check if http_range is sent by browser (or download manager)
        if (isset($_SERVER['HTTP_RANGE']) && $fsize > 0) {
            list($bytes, $http_range) = explode("=", $_SERVER['HTTP_RANGE']);

            $tmp = explode('-', $http_range);
            $tmp = array_shift($tmp);
            $set_pointer = intval($tmp);

            $new_length = $fsize - $set_pointer;

            header("Accept-Ranges: bytes");
            header("Accept-Ranges: 0-$fsize");
            $proto = isset($_SERVER['SERVER_PROTOCOL'])?$_SERVER['SERVER_PROTOCOL']:'HTTP/1.1';
            header("{$proto} 206 Partial Content");

            header("Content-Length: $new_length");
            header("Content-Range: bytes $http_range-$fsize/$org_size");

            fseek($file, $set_pointer);

        } else {
            header("Content-Length: " . $fsize);
        }
        $packet = 1;

        if ($file) {
            while (!(connection_aborted() || connection_status() == 1) && $fsize > 0) {

                $parallel_download = (int)get_option('__wpdm_parallel_download', 1);
                if($parallel_download === 0)
                    \WPDM\TempStorage::set("download.".wpdm_get_client_ip(), 1, 15);

                if ($fsize > $buffer)
                    echo fread($file, $buffer);
                else
                    echo fread($file, $fsize);
                if( function_exists('ob_get_level') && ob_get_level() > 0 )  @ob_flush();
                @flush();
                $fsize -= $buffer;
                $bandwidth += $buffer;
                if ($speed > 0 && ($bandwidth > $speed * $packet * 1024)) {
                    sleep(1);
                    $packet++;
                }


            }
            $package['downloaded_file_size'] = $fsize;
            //add_action('wpdm_download_completed', $package);
            @fclose($file);
        }

        return;

    }

    /**
     * @usage Download any content as a file
     * @param $filename
     * @param $content
     */
    public static function downloadData($filename, $content){
        @ob_end_clean();
        nocache_headers();
        $filetype = wp_check_filetype($filename);
        header( "X-Robots-Tag: noindex, nofollow", true );
        header("Robots: none");
        header("Content-Description: File Transfer");
        header("Content-Type: {$filetype['type']}");
        header("Content-disposition: attachment;filename=\"$filename\"");
        header("Content-Transfer-Encoding: Binary");
        header("Content-Length: " . strlen($content));
        echo $content;
    }

    /**
     * @usage Sends download headers only
     * @param $filename
     * @param int $size
     */
    public static function downloadHeaders($filename, $size = null){
        @ob_end_clean();
        $filetype = wp_check_filetype($filename);
        header("Content-Description: File Transfer");
        header("Content-Type: {$filetype['type']}");
        header("Content-disposition: attachment;filename=\"$filename\"");
        header("Content-Transfer-Encoding: Binary");
        if($size)
            header("Content-Length: " . $size);
    }


    /**
     * @usage Download any content as a file
     * @param $filename
     * @param $content
     */
    public static function mkDir($path, $mode = 0777, $recur = false){
        mkdir($path, $mode, $recur);
    }

    /**
     * @usage Create ZIP from given file list
     * @param $files
     * @param $zipname
     * @return bool|string
     */
    public static function zipFiles($files, $zipname){

        $zipped = (basename($zipname) == $zipname)? WPDM_CACHE_DIR.sanitize_file_name($zipname):$zipname;

        if(!strpos($zipname, '.zip')) $zipped .= '.zip';

        if(file_exists($zipped))
            unlink($zipped);

        if(count($files) <1 ) return false;

        $zip = new \ZipArchive();
        if ($zip->open($zipped, \ZIPARCHIVE::CREATE) !== TRUE) {
            return false;
        }
        foreach ($files as $file) {
            $file = trim($file);
            if (file_exists(UPLOAD_DIR . $file)) {
                $fnm = preg_replace("/^[0-9]+?wpdm_/", "", $file);
                $zip->addFile(UPLOAD_DIR . $file, $fnm);
            } else if (file_exists($file)){
                $fname = basename($file);
                $zip->addFile($file, $fname);
            }
            //else if (file_exists(WP_CONTENT_DIR . end($tmp = explode("wp-content", $file)))) //path fix on site move
            //    $zip->addFile(WP_CONTENT_DIR . end($tmp = explode("wp-content", $file)), wpdm_basename($file));
        }
        $zip->close();

        return $zipped;
    }

    /**
     * Cache remote file to local directory and return local file path
     * @param mixed $url
     * @param mixed $filename
     * @return string $path
     */
    public static function copyURL($url, $filename = '', $overwrite = true){
        $filename = $filename ? $filename : end($tmp = explode('/', $url));
        $path = WPDM_CACHE_DIR . $filename;
        if(file_exists($path) && !$overwrite) return $path;
        $fp = fopen($path, 'w');
        if(!function_exists('curl_init')) \WPDM_Messages::Error('<b>cURL</b> is not active or installed or not functioning properly in your server',1);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        $data = curl_exec($ch);
        curl_close($ch);
        fclose($fp);
        return $path;
    }

    /**
     * @param $dir
     * @param bool|true $recur
     * @return array
     */
    public static function scanDir($dir, $recur = true, $abspath = true){
        $dir = realpath($dir)."/";
        if($dir == '/' || $dir == '') return array();
        $tmpfiles = file_exists($dir)?array_diff( scandir( $dir ), array( ".", "..", ".DS_Store" ) ):array();
        $files = array();
        foreach($tmpfiles as $file){
            if( is_dir($dir.$file) && $recur == true) $files = array_merge($files, self::scanDir($dir.$file, true));
            else
                $files[] = $abspath?$dir.$file:$file;
        }
        return $files;
    }

    /**
     * @param $dir
     * @param bool|true $recur
     * @return array
     */
    public static function listFiles($dir, $recur = true, $abspath = true){
        $dir = realpath($dir)."/";
        if($dir == '/' || $dir == '') return array();
        $tmpfiles = file_exists($dir)?array_diff( scandir( $dir ), array( ".", ".." ) ):array();
        $files = array();
        foreach($tmpfiles as $file){
            if( is_dir($dir.$file) && $recur == true) $files = array_merge($files, self::scanDir($dir.$file, true));
            else if(!is_dir($dir.$file))
                $files[] = $abspath?$dir.$file:$file;
        }
        return $files;
    }

    /**
     * @param $dir
     * @param bool|true $recur
     * @return array
     */
    public static function subDirs($dir, $abspath = true){
        $dir = realpath($dir)."/";
        if($dir == '/' || $dir == '') return array();
        $tmpfiles = file_exists($dir)?array_diff( scandir( $dir ), array( ".", ".." ) ):array();
        $subdirs = array();
        foreach($tmpfiles as $file){
            if( is_dir($dir.$file)) $subdirs[] = $abspath?$dir.$file:$file;

        }
        return $subdirs;
    }


    /**
     * @param $dir
     * @param bool|true $recur
     * @return array|bool
     */
    public static function deleteFiles($dir, $recur = true){
        $dir = realpath($dir)."/";
        if($dir == '/' || $dir == '') return array();
        $tmpfiles = file_exists($dir)?array_diff( scandir( $dir ), array( ".", ".." ) ):array();
        $files = array();
        foreach($tmpfiles as $file){
            if( is_dir($dir.$file) && $recur == true) $files = array_merge($files, self::scanDir($dir.$file, true));
            else
                @unlink($dir.$file);
        }
        return true;
    }

    /**
     * @param $src
     * @param $dst
     */
    public static function copyDir( $src , $dst ) {
        $src = realpath( $src );
        $dir = opendir( $src );

        $dst = realpath( $dst ) . '/' . basename($src);
        @mkdir( $dst );

        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    self::copyDir($src . '/' . $file,$dst . '/' . $file);
                }
                else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }



    public static function imageThumbnail($path, $width, $height, $crop = WPDM_USE_GLOBAL, $usecache = true){
        $opath = $path;
        $abspath = str_replace("\\","/", ABSPATH);
        $cachedir = str_replace("\\","/", WPDM_CACHE_DIR);
        $path = str_replace("\\","/", $path);
        if(is_ssl()) $path = str_replace("http://","https://", $path);
        else  $path = str_replace("https://","http://", $path);
        $path = str_replace(site_url('/'), $abspath, $path);

        $crop = $crop === WPDM_USE_GLOBAL ? get_option('__wpdm_crop_thumbs', false) : $crop;

        if(strpos($path, '.wp.com')) {
            $path = explode("?", $path);
            $path = $path[0]."?resize={$width},{$height}";
            return $path;
        }
        if(strpos($path, '://')) return $path;
        if (!file_exists($path)) return WPDM_BASE_URL.'assets/images/404.jpg';
        //if (!file_exists($path) && strstr($opath, "://")) return $opath;

        $name_p = explode(".", $path);
        $ext = "." . end($name_p);
        $filename = basename($path);
        $thumbpath = $cachedir . str_replace($ext, "-{$width}x{$height}" . $ext, $filename);

        if (file_exists($thumbpath) && $usecache) {
            $thumbpath = str_replace($abspath, site_url('/'), $thumbpath);
            return $thumbpath;
        }
        $image = wp_get_image_editor($path);
        $fullurl = str_replace(ABSPATH, home_url('/'), $path);
        if (!is_wp_error($image)) {
            //if ( is_wp_error( $image->resize( $width, $height, true ) ) ) return $fullurl;
            $image->resize( $width, $height, $crop );
            $image->save($thumbpath);

        }
        else
            return $path;

        $thumb_size = $image->get_size();
        if($thumb_size['width'] < $width || $thumb_size['height'] < $height) {
            if($height == 0) $height = $thumb_size['height'];
            $_image_back = imagecreatetruecolor($width, $height);
            $color = imagecolorallocatealpha($_image_back, 255, 255, 255, 127);
            imagefill($_image_back, 0, 0, $color);
            if (strstr($thumbpath, ".png"))
                $_image_top = imagecreatefrompng($thumbpath);
            if (strstr($thumbpath, ".gif"))
                $_image_top = imagecreatefromgif($thumbpath);
            if (strstr($thumbpath, ".jpg") || strstr($thumbpath, ".jpeg"))
                $_image_top = imagecreatefromjpeg($thumbpath);
            if(!$_image_top) return $thumbpath;
            $imgw = imagesx($_image_top);
            $imgh = imagesy($_image_top);
            $posx = (int)(($width - $imgw) / 2);
            $posy = (int)(($height - $imgh) / 2);
            imagecopy($_image_back, $_image_top, $posx, $posy, 0, 0, $imgw, $imgh);
            imagepng($_image_back, $thumbpath);
            imagedestroy($_image_back);
        }

        $thumbpath = str_replace("\\","/", $thumbpath);
        $thumbpath = str_replace($abspath, site_url('/'), $thumbpath);

        return $thumbpath;
    }

    /**
     * @param $pdf
     * @param $id
     * @return string
     * @usage Generates thumbnail from PDF file. [ From v4.1.3 ]
     */
    public static function pdfThumbnail($pdf, $id){
        $pdfurl = '';
        if(strpos($pdf, "://")) { $pdfurl = $pdf; $pdf = str_replace(home_url('/'), ABSPATH, $pdf); }
        if($pdf == $pdfurl) return '';
        if(file_exists($pdf)) $source = $pdf;
        else $source = UPLOAD_DIR.$pdf;
        if(!file_exists(WPDM_CACHE_DIR. "pdfthumbs/")) {
            @mkdir(WPDM_CACHE_DIR . "pdfthumbs/", 0755);
            @chmod(WPDM_CACHE_DIR . "pdfthumbs/", 0755);
        }
        $dest = WPDM_CACHE_DIR. "pdfthumbs/{$id}.png";
        $durl = WPDM_CACHE_URL."pdfthumbs/{$id}.png";
        $ext = explode(".", $source);
        $ext = end($ext);
        if($ext!='pdf') return '';
        if(file_exists($dest)) return $durl;
        if(!file_exists($source))
            $source = utf8_encode($source);
        $source = $source.'[0]';
        if(!class_exists('Imagick')) return "Error: Imagick is not installed properly";
        try{
            $image = new \imagick($source);
            $image->setResolution( 800, 0 );
            $image->setImageFormat( "png" );
            $image->writeImage($dest);
        } catch(\Exception $e){
            //return 'https://via.placeholder.com/180x240/EEEEEE/888888?text=PDF+Preview';
            return '';
        }
        return $durl;
    }

    /**
     * @usgae Block http access to a dir
     * @param $dir
     */
    public static function blockHTTPAccess($dir, $fileType = '*'){
        $cont = "RewriteEngine On\r\n<Files {$fileType}>\r\nDeny from all\r\n</Files>\r\n";
        @file_put_contents($dir . '/.htaccess', $cont);
    }

    /**
     * @usage Google Doc Preview Embed
     * @param $url
     * @return string
     */
    public static function docPreview($url, $ext = ''){
        if($ext == 'pdf')
            return '<iframe src="https://docs.google.com/viewer?url='.urlencode($url).'&embedded=true" width="100%" height="600" style="border: none;"></iframe>';
        else
            return '<iframe src="https://view.officeapps.live.com/op/view.aspx?src='.urlencode($url).'&embedded=true" width="100%" height="600" style="border: none;"></iframe>';


    }

    public static function fullPath($file, $pid){
        $post = get_post($pid);
        $user = get_user_by('id', $post->post_author);
        $user_upload_dir = UPLOAD_DIR . $user->user_login . '/';
        if (file_exists(UPLOAD_DIR . $file))
            $fullpath = UPLOAD_DIR . $file;
        else if (file_exists($user_upload_dir.$file))
            $fullpath = $user_upload_dir.$file;
        else if (file_exists($file))
            $fullpath = $file;
        else
            $fullpath = '';
        return $fullpath;
    }

    public static function mediaURL($pid, $fileID, $fileName = ''){
        if($fileName == '') {
            $files = \WPDM\Package::getFiles($pid);
            $fileName = wpdm_basename($files[$fileID]);
        }
        //$key = uniqid();
        //$exp = array('use' => 1, 'expire' => time()+600);
        //TempStorage::set("__wpdmkey_{$key}", $exp);
        //return home_url("/wpdm-media/{$pid}/{$fileID}/{$fileName}?_wpdmkey={$key}");
        return Package::expirableDownloadLink($pid, 5, 1800)."&ind={$fileID}&file={$fileName}";
    }

    static function html2pdf($html_content, $pdf_file_name){

        $result = wp_remote_post("https://urlxpdf.herokuapp.com/api/render",
            array(
                'method' => 'POST',
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'body' => $html_content,
                'cookies' => array(),
                'headers' => array(
                    'Content-Type' => 'text/html'
                )
            )
        );

        self::downloadData($pdf_file_name, wp_remote_retrieve_body($result));
        die();
    }

    static function fileTypeIconSVG($ext, $color){
        ob_start();
        $ext = strtoupper($ext);
        $color_rgba = wpdm_hex2rgb($color);
        ?>
        <svg id="Layer_1" style="enable-background:new 0 0 512 512;" version="1.1" viewBox="0 0 512 512" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><style type="text/css">
                .st0{fill: rgba(<?php echo $color_rgba; ?>, 0.3);}
                .st1{fill: rgba(<?php echo $color_rgba; ?>, 0.9);}
                .st2{fill: <?php echo $color; ?>;}
                .st3{fill:#FFFFFF;}
            </style><g id="XMLID_168_"><g id="XMLID_83_"><polygon class="st0" id="XMLID_87_" points="330.7,6 87.9,6 87.9,506 449.2,506 449.2,122.8   "/><polygon class="st1" id="XMLID_86_" points="330.7,6 449.2,122.8 330.7,122.8   "/><rect class="st1" height="156.1" id="XMLID_85_" width="329" x="62.8" y="298.8"/><polygon class="st2" id="XMLID_84_" points="62.8,454.9 87.9,476.1 87.9,454.9   "/></g><g xmlns="http://www.w3.org/2000/svg" id="XMLID_3113_">
                    <text x="20%" fill="white" style="font-family: sans-serif;font-size: 725%;font-weight: bold;" y="82%"><?php echo $ext; ?></text>
                </g></g>
        </svg>
        <?php
        $svg = ob_get_clean();
        echo "data:image/svg+xml;base64,".base64_encode($svg);
    }

    public static function instantDownloadURL($file, $expire = 3600){
        $id = uniqid();
        TempStorage::set("__wpdm_instant_download_{$id}", $file, $expire);
        return home_url("/?wpdmidl={$id}");
    }


}
