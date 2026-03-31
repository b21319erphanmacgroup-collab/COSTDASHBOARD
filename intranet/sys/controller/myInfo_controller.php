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
	//$memberID=$_COOKIE['CK_memberID'];
	$memberID=$_SESSION['CK_memberID'];	//쿠키정보 세션으로 대체 250626 김진선
	/*---------------------------------------------*/
	$GoLogic = new MyInfoLogic($smarty);
	/*---------------------------------------------*/
	if($ActionMode=="myInfo"){					//개인정보 상세페이지로 이동

		$myip   = $_SERVER["REMOTE_ADDR"];   // 접근 ip 저장
		if($myip=="1.229.157.66"){
			$GoLogic->DefaultInfo();				//기본정보
			$GoLogic->LateInfoAction();				//지각정보
			$GoLogic->OverworkData();				//연장근무 일수
			$GoLogic->OverworkData_time_test();				//연장근무 시간 : 20170516 추가
			$GoLogic->ListAction();					//VIEW tpl 지정
		}else{
			$GoLogic->DefaultInfo();				//기본정보
			$GoLogic->LateInfoAction();				//지각정보
			$GoLogic->OverworkData();				//연장근무 일수
			$GoLogic->OverworkData_time();				//연장근무 시간 : 20170516 추가
			$GoLogic->ListAction();					//VIEW tpl 지정
		}

	}else if($ActionMode=="myInfo_detail"){		//페이지이동 : 인사카드
		$GoLogic->DetailListAction();
		
	}else if($ActionMode=="myInfo_update"){		//개인정보 : 수정
		$GoLogic->MyInfoSave();

	}else if($ActionMode=="checkPwMain"){		//페이지이동 : 패스워드 체크
		$GoLogic->CheckPwMainPage();

	}else if($ActionMode=="checkPw"){			//패스워드 체크 DB 실행
		$GoLogic->CheckPw();
		
	}else if($ActionMode=="EditMailPW"){			//페이지이동 : 이메일 패스워드 관리
		$GoLogic->EditMailPW();
		
	}else if($ActionMode=="EditMailPW_Action"){			//페이지이동 : 이메일 패스워드 관리
		$GoLogic->EditMailPW_Action();
		
	}else if($ActionMode=="MOVE_PW_MANAGE"){			//페이지이동 :  패스워드 관리 : 2022.02.13
		$GoLogic->MOVE_PW_MANAGE();

	}else if($ActionMode=="DB_PW_MANAGE"){			//패스워드 관리 DB실행 : 2022.02.13
		$GoLogic->DB_PW_MANAGE();
		
	}else if($ActionMode=="UserPWChange"){			//페이지이동 : 사용자 메뉴별 패스워드 관리
		$GoLogic->UserPWChange();

	}else{
		$GoLogic->ListAction();
	}


?>


