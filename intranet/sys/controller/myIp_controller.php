<?php
	/* **********************************
	* 메인화면 : LEFT : 개인정보 : IP입력
	* 사원정보 조회
	* ------------------------------------
	*************************************** */
	session_start();

	/* DB설정 -------------------------------------*/
	include "../../sys/inc/dbcon.inc";
	/* 스마티 설정---------------------------------*/
	include "../../../SmartyConfig.php";
	/* 로직클래스----------------------------------*/
	include "../../sys/model/MyIpLogic.php";
	/*---------------------------------------------*/
	require_once($SmartyClassPath);
	/*---------------------------------------------*/
    $smarty = new Smarty($smarty);
	$smarty->template_dir	= $SmartyClass_TemplateDir;
    $smarty->compile_dir	= $SmartyClass_CompileDir;
    $smarty->config_dir		= $SmartyClass_ConfigDir;
    $smarty->cache_dir		= $SmartyClass_CacheDir;
	/*---------------------------------------------*/
	$GoLogic = new MyIpLogic($smarty);
	/*---------------------------------------------*/
	if($ActionMode=="Ipinsert_page"){		//IP 입력 페이지
		$GoLogic->Ipinsert_page();
	}else if($ActionMode=="insert"){	//인사카드 입력 DB 실행
		$GoLogic->InsertAction();
	}else if($ActionMode=="ip_check"){	//IP 체크
		$GoLogic->ip_check();
	}else{
		$GoLogic->Ipinsert_page();
	}
?>
