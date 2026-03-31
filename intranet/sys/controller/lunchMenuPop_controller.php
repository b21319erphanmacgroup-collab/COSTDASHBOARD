<?php
	/* **********************************
	* 메인화면 : LEFT : 개인정보 : 인사카드
	* 사원정보 조회 
	* ------------------------------------
	* 2014-12-   :
	* 2014-12-18 : 세션값을 쿠키값으로 대체( $_COOKIE['CK_memberID'] )
	* 2014-12-16 : 파일정리: SUK 
	*************************************** */
	session_start();
?>
<?php

	/* DB설정 -------------------------------------*/
	include "../../sys/inc/dbcon.inc";
	/* 스마티 설정---------------------------------*/
	include "../../../SmartyConfig.php";
	/* 로직클래스----------------------------------*/
	include "../../sys/model/LunchMenuPopLogic.php";
	/*---------------------------------------------*/
	require_once($SmartyClassPath);
	/*---------------------------------------------*/
    $smarty = new Smarty($smarty);
	$smarty->template_dir	= $SmartyClass_TemplateDir;
    $smarty->compile_dir	= $SmartyClass_CompileDir;
    $smarty->config_dir		= $SmartyClass_ConfigDir;
    $smarty->cache_dir		= $SmartyClass_CacheDir;
	/*---------------------------------------------*/
	$GoLogic = new LunchMenuPopLogic($smarty);
	/*---------------------------------------------*/

	if($ActionMode=="lunchPop"){		//인사카드 입력 팝업창으로 이동
		$GoLogic->lunchPop();
	}else{
		echo "ActionMode 미정";
	}

?>
