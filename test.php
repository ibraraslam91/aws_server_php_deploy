<?php
/**
 * Created by PhpStorm.
 * User: ibrar
 * Date: 4/13/17
 * Time: 4:50 PM
 */

$url = "https://www.olx.com.pk/item/job-opportunity-IDW8kpt.html#8569bd12c5;promoted";

$output = file_get_contents($url);
libxml_use_internal_errors(TRUE);
if(!empty($output)) {
    $deepDoc = new DOMDocument();
    error_reporting(E_ALL ^ E_WARNING);
    $deepDoc->loadHTML($output);
    $deepXpath = new DOMXPath($deepDoc);
    $title = $deepXpath->query('//h1[@class="brkword lheight28"]')->item(0)->nodeValue;
    print $title;
    print "</br>";
    $price = $deepXpath->query('//strong[@class="x-large margintop7 block not-arranged"]')->item(0)->textContent;
    if(!isset($price)){
        print "Is set";
    }
    print $price;
    print "</br>";
    $location = $deepXpath->query('//strong[@class="c2b small"]')->item(0)->nodeValue;
    print $location;
    print "</br>";
    $id_ad = $deepXpath->query('//span[@class="rel inlblk"]')->item(0)->nodeValue;
    print $id_ad;
    print "</br>";
    $name = $deepXpath->query('//span[@class="block color-5 brkword xx-large"]')->item(0)->nodeValue;
    print $name;
    print "</br>";
    $dec = $deepXpath->query('//p[@class="pding10 lheight20 large"]')->item(0)->nodeValue;
    print $dec;
    print "</br>";
    $phoneN = $deepXpath->query('//strong[@class="large lheight20 fnormal  "]')->item(0)->nodeValue;
    $content = "Price : " . $price . " \nPost by : " . $name . "Contact Number# : " . $phoneN . "\nDec : " . $dec . "\n" . $location;

}

?>