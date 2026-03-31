<?
	/***************************************
	* 업무검토
	* ------------------------------------
	* 2025.12.05	김한결 작성
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
	if($_SESSION["memberID"]=="M22014"){
		include "../../sys/model/WorkCheck_logic_dev.php";
	}
	else{
		include "../../sys/model/WorkCheck_logic.php";
	}

	require_once($SmartyClassPath);

	$smarty = new Smarty($smarty);
	$smarty->template_dir	= $SmartyClass_TemplateDir;
	$smarty->compile_dir	= $SmartyClass_CompileDir;
	$smarty->config_dir		= $SmartyClass_ConfigDir;
	$smarty->cache_dir		= $SmartyClass_CacheDir;
	
	$CurrentLogic = new WorkCheck();
	
	if($ActionMode=="ViewPage"){
		$CurrentLogic->ViewPage();      //페이지 접근
	}

?>
