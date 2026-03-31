<?php
	/***************************************
	* 배차현황
	* ------------------------------------
	* 2014-12-16 : 파일정리: JYJ
	****************************************/
	session_start();
	if($_GET['memberID'] !="")
	{
		$memberID = $_GET['memberID'];
	}
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
	//include "../model/schedule_Meetingroom_logic.php";

	if( $memberID=="M20330a" or $memberID=="T03225a" ){
		include "../model/schedule_Meetingroom_logic_jmj.php";
	}else{
		include "../model/schedule_Meetingroom_logic.php";
	}

	require_once($SmartyClassPath);

    $smarty = new Smarty($smarty);
	$smarty->template_dir	=$SmartyClass_TemplateDir;
    $smarty->compile_dir	=$SmartyClass_CompileDir;
    $smarty->config_dir		=$SmartyClass_ConfigDir;
    $smarty->cache_dir		=$SmartyClass_CacheDir;

	$CurrentLogic=new ScheduleMeetingroomLogic($smarty);

	if($ActionMode=="insert_page")													//배차신청 입력창
		$CurrentLogic->InsertPage();
	else if($ActionMode=="insert")													//배차신청 저장 (INSERT)
		$CurrentLogic->InsertAction();
	else if($ActionMode=="update_page" or $ActionMode=="delete_page")               //배차신청 읽기/수정/삭제 창
		$CurrentLogic->UpdateReadPage();
	else if($ActionMode=="update")													//배차신청 저장 (UPDATE)
		$CurrentLogic->UpdateAction();
	else if($ActionMode=="delete")													//배차신청 삭제 (DELETE)
		$CurrentLogic->DeleteAction();
	else if($ActionMode=="check_time")												//시간체크
		$CurrentLogic->CheckTime();
	else
		$CurrentLogic->View();														//배차현황 LIST
?>
