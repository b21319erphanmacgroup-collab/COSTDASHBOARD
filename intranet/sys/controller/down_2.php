<?

$file = iconv("UTF-8","EUC-KR",$file) ? iconv("UTF-8","EUC-KR",$file) : $file;
//echo $file;
$divfile=explode("/",$file);
$divnum = count($divfile);

//$dnfile =파일명 
$dnfile=$divfile[$divnum-1];


header('Content-Disposition: attachment; filename='.$dnfile.""); 
header('Content-Length: '.filesize($file));
readfile($file);


?>

