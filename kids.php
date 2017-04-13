<?php
/**
 * Created by PhpStorm.
 * User: ibrar
 * Date: 3/18/17
 * Time: 1:44 PM
 */

require(dirname(__FILE__) . '/wp-load.php');
require 'wp-admin/includes/file.php';
require 'wp-admin/includes/media.php';
require 'vendor/autoload.php';
use \Statickidz\GoogleTranslate;

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = 'qweasdzx';
$DB_NAME = 'wp2';
$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

$query = "Select * from table_kids ORDER BY id LIMIT 10 ";

$result = $mysqli->query($query) or die($mysqli->error.__LINE__);

if($result->num_rows>0){
    while($row = $result->fetch_assoc()) {
        $url = $row['hrefs'];
        $imageUrl = $row['imageUrl'];
        $rowID = $row['id'];
        $subCat = $row['subCh'];
        crawleData($url,$imageUrl,$rowID,$subCat);
    }
}


function crawleData($url,$imageUrl,$rowID,$subCat){
    $output = file_get_contents($url);
    libxml_use_internal_errors(TRUE);
    if(!empty($output)){
        $deepDoc = new DOMDocument();
        error_reporting(E_ALL ^ E_WARNING);
        $deepDoc->loadHTML($output);
        $deepXpath = new DOMXPath($deepDoc);
        $titleRaw = $deepXpath->query('//h1[@class="brkword lheight28"]')->item(0)->nodeValue;
        $price = $deepXpath->query('//strong[@class="xxxx-large margintop7 block not-arranged"]')->item(0)->nodeValue;
        $location = $deepXpath->query('//strong[@class="c2b small"]')->item(0)->nodeValue;
        $id_ad = $deepXpath->query('//span[@class="rel inlblk"]')->item(0)->nodeValue;
        $name = $deepXpath->query('//span[@class="block color-5 brkword xx-large"]')->item(0)->nodeValue;
        $decRaw = $deepXpath->query('//p[@class="pding10 lheight20 large"]')->item(0)->nodeValue;
        $phoneN = $deepXpath->query('//strong[@class="large lheight20 fnormal  "]')->item(0)->nodeValue;

        $titleRaw2 = translate_to_es_func($titleRaw);
        $decRaw2 = translate_to_es_func($decRaw);

        $titleRaw3 = translate_to_en_func($titleRaw2);
        $dec = translate_to_en_func($decRaw2);

        $title = $titleRaw3." ".get_suffix($dec)." ".$location;

        print $title;

        $content = "Price : ".$price." \nPost by : ".$name."Contact Number# : ".$phoneN."\nDec : ".$dec."\n".$location;
        $post = array();
        $post['post_status']   = 'publish';
        $post['post_type']     = 'post';
        $post['post_title']    = $title;
        $post['post_content']  = $content;
        $post['post_author']   = 1;
        $post['post_category']   = array( $subCat);
        $id = wp_insert_post($post);
        if($id){
            Generate_Featured_Image($imageUrl,$id,'image',$id_ad);
            removeRow($rowID);
        }
    }
}

function get_suffix($text){
    $words = explode(" ",$text);
    $lenght = sizeof($words);
    if($lenght<4){
        return $text;
    }else{
        $last_index = sizeof($words)-3;
        $randIndex = rand(0,$last_index);
        $result = implode(" ",array_slice($words,$randIndex,3));
        return $result;
    }
}

function translate_to_es_func($text){

    $source = 'en';
    $target = 'es';
    $trans = new GoogleTranslate();
    $result = $trans->translate($source, $target, $text);
    print 'from = '.$text."</br>";
    print 'result = '.$result."</br>";
    return $result;
}

function translate_to_en_func($text){

    $source = 'es';
    $target = 'en';
    $trans = new GoogleTranslate();
    $result = $trans->translate($source, $target, $text);
    print 'from = '.$text."</br>";
    print 'result = '.$result."</br>";
    return $result;
}


function removeRow($rowID){
    global $mysqli;
    $query1 = "DELETE FROM table_books_sports_hobbies WHERE id=$rowID;";
    $result = $mysqli->query($query1) or die($mysqli->error.__LINE__);
}


function Generate_Featured_Image( $file, $post_id, $desc ,$id_ad){
    $image_url        = $file;
    $image_name       = $id_ad.'.png';
    $upload_dir       = wp_upload_dir();
    $image_data       = file_get_contents($image_url);
    $unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name );
    $filename         = basename( $unique_file_name );
    if( wp_mkdir_p( $upload_dir['path'] ) ) {
        $file = $upload_dir['path'] . '/' . $filename;
    } else {
        $file = $upload_dir['basedir'] . '/' . $filename;
    }
    file_put_contents( $file, $image_data );
    $wp_filetype = wp_check_filetype( $filename, null );
    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title'     => sanitize_file_name( $filename ),
        'post_content'   => '',
        'post_status'    => 'inherit'
    );
    $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
    wp_update_attachment_metadata( $attach_id, $attach_data );
    set_post_thumbnail( $post_id, $attach_id );
}
?>
?>