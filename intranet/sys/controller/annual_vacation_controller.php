<?php
	/***************************************
	* 연차이력
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
	$LoginIn->GetLoginStatus();

	/* ------------------------------------- */
	include "../../sys/inc/dbcon.inc";
	include "../../../SmartyConfig.php";


	if( $_GET['memberID'] == 'M21464'){
		include "../../sys/model/annual_vacation_logic.php";
		//include "../../sys/model/annual_vacation_logic_test.php";
	}else{
		include "../../sys/model/annual_vacation_logic.php";
	}

	//include "../../sys/model/annual_vacation_logic.php";
	require_once($SmartyClassPath);
	/* ------------------------------------- */
	$smarty = new Smarty($smarty);
	$smarty->template_dir	= $SmartyClass_TemplateDir;
	$smarty->compile_dir	= $SmartyClass_CompileDir;
	$smarty->config_dir		= $SmartyClass_ConfigDir;
	$smarty->cache_dir		= $SmartyClass_CacheDir;
	/* ------------------------------------- */
	$CurrentLogic = new VacationBoard();
	/* ------------------------------------- */
	if($ActionMode=="detail"){
		$CurrentLogic->VacationDetailList();   //연차이력 상세
	}else{
		$CurrentLogic->VacationBoardList();    //연차이력 LIST
	}
	/* ------------------------------------- */
?>
