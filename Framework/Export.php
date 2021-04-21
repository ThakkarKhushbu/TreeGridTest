<?php 
$name = array_key_exists("ExportName",$_REQUEST) ? $_REQUEST["ExportName"] : ""; 
$ext = array_key_exists("ExportFormat",$_REQUEST) ? $_REQUEST["ExportFormat"] : ""; 
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"" . $name . "." . $ext . "\"");
header("Cache-Control: max-age=1; must-revalidate");
$XML = array_key_exists("Data",$_REQUEST) ? $_REQUEST["Data"] : "";
if(strpos($XML,"<")===false || strpos($XML,"&lt;")===false) echo(base64_decode($XML));
else echo html_entity_decode($XML);
?>