<?php
	/* **********************************
	* 메인화면 : LEFT : 개인정보 
	* 개인정보 상세보기
	* ------------------------------------
	* 2014-12-   :
	* 2014-12-18 : 세션-> 쿠키값 단계별으로 체크 : SUK
	* 2014-12-16 : 파일정리: SUK 
	*************************************** */ 
	$get_memberID	= $_GET['memberID']; 
	/*---------------------------------------------*/
	require('../../sys/util/LoginInfomation.php');
	$LoginIn = new LoginInfomation();
	$LoginIn->GetLoginStatus();
	/*---------------------------------------------*/
	//DB설정 //////////////////////////////////////
	include "../../sys/inc/dbcon.inc";
	//include "../../sys/inc/function_intranet.php";  
	include "../../../SmartyConfig.php";
	include "../../sys/model/MyInfoLogic.php";
	/*---------------------------------------------*/
	require_once($SmartyClassPath);
	/*---------------------------------------------*/
    $smarty = new Smarty($smarty);
	$smarty->template_dir	= $SmartyClass_TemplateDir;
    $smarty->compile_dir	= $SmartyClass_CompileDir;
    $smarty->config_dir		= $SmartyClass_ConfigDir;
    $smarty->cache_dir		= $SmartyClass_CacheDir;
	/*---------------------------------------------*/
	$memberID=$_COOKIE['CK_memberID'];
	/*---------------------------------------------*/
	$GoLogic = new MyInfoLogic($smarty);
	/*---------------------------------------------*/
	if($ActionMode=="myInfo"){					//개인정보 상세페이지로 이동
		$GoLogic->DefaultInfo();				//기본정보
		$GoLogic->LateInfoAction();				//지각정보
		$GoLogic->OverworkData();				//연장근무 일수
		$GoLogic->ListAction();					//VIEW tpl 지정

	}else if($ActionMode=="myInfo_detail"){		//페이지이동 : 인사카드
		$GoLogic->DetailListAction();

	}else if($ActionMode=="checkPwMain"){		//페이지이동 : 패스워드 체크
		$GoLogic->CheckPwMainPage();

	}else if($ActionMode=="checkPw"){			//패스워드 체크 DB 실행
		$GoLogic->CheckPw();

	}else{
		$GoLogic->ListAction();
	}


?>


