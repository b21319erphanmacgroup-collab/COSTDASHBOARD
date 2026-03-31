<?
	/***************************************
	* 근태이력 상세
	* ------------------------------------
	* 2014-12-16 : 파일정리: JYJ
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

	include "../../sys/inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	if($memberID!="M22014"){
	include "../../sys/model/person_login_logic.php";
	}
	elseif($memberID=="M22014"){
		include "../../sys/model/person_login_logic.php";
	}

	require_once($SmartyClassPath);

	$smarty = new Smarty($smarty);
	$smarty->template_dir	= $SmartyClass_TemplateDir;
	$smarty->compile_dir	= $SmartyClass_CompileDir;
	$smarty->config_dir		= $SmartyClass_ConfigDir;
	$smarty->cache_dir		= $SmartyClass_CacheDir;
		
	$GoLogic = new PersonLoginBoard();

	if($ActionMode=="list")				//최근업무 내용 목록페이지로 이동
		$GoLogic->PersonLoginList();
	else
		$GoLogic->PersonLoginList();    //근태이력 상세

?>
