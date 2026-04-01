<?
//$file=iconv("EUC-KR", "UTF-8",$file);
//$file=iconv("UTF-8", "EUC-KR",$file);


$file = "./".$file_name; 
//$file = "../images/upload/$file_name";

echo $file;

//$file_size = filesize($file); 
$file_utf8 = iconv("EUC-KR", "UTF-8",$file);

$file_utf8 = $file_utf8;

$file_size = filesize($file_utf8);

echo $file_size;

$filename = urlencode("$file_name"); 



 if(!file_exists($file_utf8)) {
echo "<br>파일이없습니다".$file;
echo "<br>".$_SERVER['PHP_SELF'];


 } else {// 파일이 있으면 다운로드
echo "<br>파일이 있습니다".$file;
echo "<br>".$file_size;
/*
echo "<br>파일이 있습니다".$file;
echo "<br>".$_SERVER['PHP_SELF'];
echo "<br>".$file_size;
*/
 }

Header("Content-type: file/unknown"); 
Header("Content-Disposition: attachment; filename=$filename"); 
Header("Content-Transfer-Encoding: binary"); 
Header("Content-Length: ".$file_size); 
Header("Content-Description: PHP3 Generated Data"); 
header("Pragma: no-cache"); 
header("Expires: 0"); 
if (is_file("$file")) { 
$fp = fopen("$file", "r"); 
if (!fpassthru($fp)) 
fclose($fp); 
}








?>
