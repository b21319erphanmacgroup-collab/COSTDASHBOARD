<?
	extract($_REQUEST);
if($test == 'test'){
	//print_r($_REQUEST);

	$file = iconv("UTF-8","EUC-KR",$file) ? iconv("UTF-8","EUC-KR",$file) : $file;
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

	header('Content-Disposition: attachment; filename='.$ori_file."");
	//header('Content-Length: '.filesize($file));
	readfile($file);


}else{
	if(mb_detect_encoding( $file ) == 'UTF-8'){
		$file = iconv("UTF-8","EUC-KR",$file);
	}
	//$file = iconv("UTF-8","EUC-KR",$file) ? iconv( mb_detect_encoding( $file ), 'EUC-KR', $file) : $file;
	//$file = iconv("UTF-8","EUC-KR",$file) ? iconv("UTF-8","EUC-KR",$file) : $file;

	$divfile=explode("/",$file);
	$divnum = count($divfile);

	//$dnfile =파일명
	$dnfile=$divfile[$divnum-1];
	if($ori_file<>""){
		$ori_file=iconv("UTF-8", "EUC-KR",$ori_file);
	}else{
		$ori_file=$dnfile;
	}

	// IE인지 HTTP_USER_AGENT로 확인
	$ie = isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'Trident') !== false);
	// EDGE인지 HTTP_USER_AGENT로 확인
	$edge = isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'Edge') !== false);

	if ($edge){
		//if(mb_detect_encoding( $ori_file ) == 'EUC-KR'){
			$ori_file = iconv("EUC-KR","UTF-8",$ori_file);
		//}
		// edge인경우 파일명 rowurlencode로 인코딩시킴
		$ori_file = rawurlencode($ori_file);
		$ori_file = preg_replace('/\./', '%2e', $ori_file, substr_count($ori_file, '.') - 1);
		// edge인 경우의 헤더 변경
		$header_cachecontrol = 'private, no-transform, no-store, must-revalidate';
		$header_pragma='no-cache';
	}else{
		if($ie) {
			// UTF-8에서 EUC-KR로 캐릭터셋 변경
			//$ori_file = iconv('utf-8', 'euc-kr', $ori_file);
			// IE인 경우 헤더 변경
			$header_cachecontrol = 'must-revalidate, post-check=0, pre-check=0';
			$header_pragma='public';
		}else{
			// IE가 아닌 경우 일반 헤더 적용
			$header_cachecontrol = 'private, no-transform, no-store, must-revalidate';
			$header_pragma='no-cache';
		}
	}
	//echo mb_detect_encoding( $ori_file );
	//$ori_file = iconv( mb_detect_encoding( $ori_file ), 'EUC-KR', $ori_file);

	//$ori_file = iconv("UTF-8", "EUC-KR",$ori_file);
	header('Content-Disposition: attachment; filename="'.$ori_file.'"');
	header("Content-Type: application/octet-stream;");
	//header('Content-Length: '.filesize($file));

	// Generate the server headers
	//header('Expires: 0');
	//header('Content-Transfer-Encoding: binary');
	header('Cache-Control: ' . $header_cachecontrol);
	//header('Content-Disposition: attachment; filename="'.$filename.'"');
	header('Pragma: '. $header_pragma);
	readfile($file);
}

?>