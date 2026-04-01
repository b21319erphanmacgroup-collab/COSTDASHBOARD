<?php
	//외부서버에서 ajax 허용
	header('Access-Control-allow-Origin:*');
	header('Content-Type: text/html; charset=UTF-8');
	extract($_REQUEST);

	/******************************************************************************
	* 페이지 로딩속도 체크 로직
	* -----------------------------------------------------------------------------
	*  작업일자   |  작업자   | 작업 내용
	* 2022-07-11  |  정명준   | 생성
	*******************************************************************************/
	include "../inc/dbcon.inc";
	global $db;
	$azsql = " INSERT INTO loading_tbl ( open_comp, loading_info ) VALUES ( '$open_comp', '$loading_info' ); ";
	//echo $azsql;
	mysql_query($azsql, $db);
?>