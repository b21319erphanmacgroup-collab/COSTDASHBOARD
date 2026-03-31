<?
	/***************************************
	* 총괄기획실 업무근태 엑셀자료 생성
	* ------------------------------------
	* 2026.03.30 이병권
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
	$LoginIn->GetLoginStatus();

	include "../../sys/inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../../sys/model/WorkCheck_logic_test.php";

	require_once($SmartyClassPath);

	$smarty = new Smarty($smarty);
	$smarty->template_dir	= $SmartyClass_TemplateDir;
	$smarty->compile_dir	= $SmartyClass_CompileDir;
	$smarty->config_dir		= $SmartyClass_ConfigDir;
	$smarty->cache_dir		= $SmartyClass_CacheDir;
	
	$CurrentLogic = new WorkCheck();
	
	if($ActionMode=="ViewPage2"){
		$CurrentLogic->ViewPage2();      //페이지 접근
	}
?>