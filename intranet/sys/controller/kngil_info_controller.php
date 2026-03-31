<?php
	/***************************************
	* 지적재산권
	* ------------------------------------
	* 2014-12-16 : 파일정리: JYJ
	****************************************/ 
	header('Access-Control-allow-Origin:*');	
	session_start();
	
	extract($_REQUEST);
	
	if($_SESSION['memberID']==""){$_SESSION['memberID']=$memberID;}
	else{$memberID=$_SESSION['memberID'];}
	
	include "../inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../model/kngil_info_logic.php";
	
	require_once($SmartyClassPath);
    
	$smarty = new Smarty($smarty);
	$smarty->template_dir	=$SmartyClass_TemplateDir;
    $smarty->compile_dir	=$SmartyClass_CompileDir;
    $smarty->config_dir		=$SmartyClass_ConfigDir;
    $smarty->cache_dir		=$SmartyClass_CacheDir;
    
    $smarty->assign('Controller',"kngil_info_controller.php");

	$CurrentLogic=new KngilInfoLogic($smarty);
	
	
	if($ActionMode == "UserInfoData"){
		$CurrentLogic->UserInfoData();
	}
	else if($ActionMode == "KngilSiteStatus"){											                     
		$CurrentLogic->KngilSiteStatus();							//KNGIL 사이트 접속 확인
	}
	else if($ActionMode == "KngilWorkStatus"){											                     
		$CurrentLogic->KngilWorkStatus();							//KNGIL 서비스 신청 내역 작업 현황
	}
	else if($ActionMode == "KngilFileDwStInput"){
		$CurrentLogic->KngilFileDwStInput();						//KNGIL테이블에 다운로드 현황 완료 처리
	}
	else if($ActionMode == "KngilFileCreateCancel"){
		$CurrentLogic->KngilFileCreateCancel();					//KNGIL 파일 생성 중 또는 생성 완료인 경우 취소 버튼 누르면 파일 다운로드 없이 새로 생성할 수 있게 바꾸는 함수
	}
	
?>
