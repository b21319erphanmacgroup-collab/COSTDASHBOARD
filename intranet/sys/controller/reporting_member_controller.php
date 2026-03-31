<?php
	/***************************************
	* 결재 수신인 설정
	* ------------------------------------
	* 2014-12-   :
	* 2014-12-16 : 파일정리: KYH
	****************************************/ 
	session_start();
	if($_SESSION['memberID']=="")
	{
		$_SESSION['memberID']=$memberID;
	}else{
		$memberID=$_SESSION['memberID'];
	}
	require('../util/LoginInfomation.php');
	$LoginIn = new LoginInfomation();
	$LoginIn->GetLoginStatusPop();

	include "../inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../model/reporting_member_logic.php";
	require_once($SmartyClassPath);
    
	$smarty = new Smarty($smarty);
	$smarty->template_dir	=$SmartyClass_TemplateDir;
    $smarty->compile_dir	=$SmartyClass_CompileDir;
    $smarty->config_dir		=$SmartyClass_ConfigDir;
    $smarty->cache_dir		=$SmartyClass_CacheDir;

	$CurrentLogic=new ReportingMemberLogic($smarty);

	
	if($ActionMode=="ReportingMemberSelect"){				
		$CurrentLogic->ReportingMemberSelect();		//업무 보고 받을 사람 목록
	}
	
	
?>