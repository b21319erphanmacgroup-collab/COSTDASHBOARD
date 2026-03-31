<?php
	/***************************************
	* 전자결재 리스트
	****************************************/

	session_start();

	if($_SESSION['memberID']=="")
	{
		$_SESSION['memberID']=$memberID;
	}else{
		$memberID=$_SESSION['memberID'];
	}
	/*
	require('../util/LoginInfomation.php');
	$LoginIn = new LoginInfomation();
	$LoginIn->GetLoginStatus();
	*/
	/* ------------------------------------- */
	include "../inc/dbcon.inc";
	include "../../../SmartyConfig.php";

	include "../model/approval_admin_logic.php";


	require_once($SmartyClassPath);
	/* ------------------------------------- */
	$smarty = new Smarty($smarty);
	$smarty->template_dir	=$SmartyClass_TemplateDir;
	$smarty->compile_dir	=$SmartyClass_CompileDir;
	$smarty->config_dir		=$SmartyClass_ConfigDir;
	$smarty->cache_dir		=$SmartyClass_CacheDir;
	/* ------------------------------------- */
	$CurrentLogic=new ApprovalAdminLogic($smarty);
	/* ------------------------------------- */


	if($ActionMode=="Admin"){
		$CurrentLogic->AdminListView();
	//}elseif($ActionMode=="ExcelDown"){
	//	$CurrentLogic->ExcelDown();
	}else{
		$CurrentLogic->AdminListView();
	}

?>
