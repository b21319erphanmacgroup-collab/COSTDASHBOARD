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
	/*---------------------------------------------*/
	$get_memberID	= $_GET['memberID']; 
	/*---------------------------------------------*/
	require('../../sys/util/LoginInfomation.php');
	$LoginIn = new LoginInfomation();
	$LoginIn->GetLoginStatus();
	/*---------------------------------------------*/
?>
<?php
	/* DB설정 -------------------------------------*/
	include "../../sys/inc/dbcon.inc";
	/* 스마티 설정---------------------------------*/
	include "../../../SmartyConfig.php";
	/* 로직클래스----------------------------------*/
	include "../../sys/model/MyCardLogic.php";
	/*---------------------------------------------*/
	require_once($SmartyClassPath);
	/*---------------------------------------------*/
    $smarty = new Smarty($smarty);
	$smarty->template_dir	= $SmartyClass_TemplateDir;
    $smarty->compile_dir	= $SmartyClass_CompileDir;
    $smarty->config_dir		= $SmartyClass_ConfigDir;
    $smarty->cache_dir		= $SmartyClass_CacheDir;
	/*---------------------------------------------*/
	$GoLogic = new MyCardLogic($smarty);
	/*---------------------------------------------*/
	if($ActionMode=="insert_page"){		//인사카드 입력 팝업창으로 이동
		$GoLogic->GetData01();
		$GoLogic->GetData02();
		$GoLogic->GetData03();
		$GoLogic->GetData04();
	}else if($ActionMode=="insert"){	//인사카드 입력 DB 실행
		$GoLogic->InsertAction();
	}else{
		$GoLogic->GetData01();
		$GoLogic->GetData02();
		$GoLogic->GetData03();
		$GoLogic->GetData04();
	}
?>
