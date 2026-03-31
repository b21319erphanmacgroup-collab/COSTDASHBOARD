<?
	extract($_REQUEST);
	//$file = iconv("UTF-8","EUC-KR",$file) ? iconv("UTF-8","EUC-KR",$file) : $file;
	if(mb_detect_encoding( $file ) == 'UTF-8'){
		$file = iconv("UTF-8","EUC-KR",$file);
	}
	//echo $file."<br>";
	$divfile=explode("/",$file);
	$divnum = count($divfile);

	//$dnfile =파일명
	$dnfile=$divfile[$divnum-1];
	if($ori_file<>"")
	{
		$ori_file=iconv("UTF-8", "EUC-KR",$ori_file);
	}
	else
	{
		$ori_file=$dnfile;
	}

	//echo 	$ori_file."<Br>";

	if(strpos($categoryName, 'jpg') !== false) {

	} elseif(strpos($categoryName, 'jpeg') !== false) {
	} elseif(strpos($categoryName, 'gif') !== false) {
	} elseif(strpos($categoryName, 'bmf') !== false) {
	} elseif(strpos($categoryName, 'png') !== false) {
	} else {
		header('Content-Disposition: attachment; filename='.$ori_file."");
		//header('Content-Length: '.filesize($file));
		readfile($file);
	}



?>

