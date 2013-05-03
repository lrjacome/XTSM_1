<?php
//$filename = $.base64_decode($_REQUEST['filename']);
//$filedata = $.base64_decode($_REQUEST['filedata']);

$filename = $_REQUEST['filename'];
$filedata = '12345';

//DOESN'T SAVE DATA, BUT DOES CREATE FOLDER INSIDE METAVIEWER.
$folder=explode('/',$filename);
If (!file_exists('sequences/'.$folder[1])) {print('creating directory'.$folder[1]);mkdir('sequences/'.$folder[1]);}
file_put_contents($filename, $filedata) or die("can't open file");
?>
