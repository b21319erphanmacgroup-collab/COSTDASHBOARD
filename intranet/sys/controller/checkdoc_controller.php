<?
	/***************************************
	* 회람 메인
	* ------------------------------------
	* 2014-12-16 : 파일정리: 
	****************************************/ 
	session_start();

	if($_GET['memberID'] <> "")
	{
		$memberID = $_GET['memberID'];
	}



	if($_SESSION['memberID']=="")
	{
		$_SESSION['memberID']=$memberID;
	}else{
		$memberID=$_SESSION['memberID'];
	}


	$filename="documnet".$chk_no.".xls";
	if($excel=="excel")
	{	
		header("Content-type:application/vnd.ms-excel");
		header("Content-Disposition:attachment; filename=$filename");
		header("Expires:0");
		header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
		header("Pragma:public");
		
	}




	require('../util/LoginInfomation.php');
	$LoginIn = new LoginInfomation();
	//$LoginIn->GetLoginStatusPop();


	include "../../sys/inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../../sys/model/checkdoc_logic.php";

	require_once($SmartyClassPath);

	$smarty = new Smarty($smarty);
	$smarty->template_dir	= $SmartyClass_TemplateDir;
	$smarty->compile_dir	= $SmartyClass_CompileDir;
	$smarty->config_dir		= $SmartyClass_ConfigDir;
	$smarty->cache_dir		= $SmartyClass_CacheDir;
	
	$CurrentLogic = new CheckDoc();

	if($ActionMode=="view")
		$CurrentLogic->ViewDoc();      //회람지보기(개인별 보기)
	else if($ActionMode=="answer")
		$CurrentLogic->AnswerDoc();      //회람지답변하기
	else if($ActionMode=="report")
		$CurrentLogic->ReportDoc();      //회람지 정보통계
	else if($ActionMode=="group")
		$CurrentLogic->GroupDoc();      //부서별 정보통계
	else if($ActionMode=="list")
		$CurrentLogic->ListDoc();      //회람지리스트보기
	else if($ActionMode=="edit")
		$CurrentLogic->EditDoc();      //회람지편집창
	else if($ActionMode=="update")
		$CurrentLogic->UpdateDoc();      //회람지편집저장
	else if($ActionMode=="save")
		$CurrentLogic->SaveDoc();      //회람지저장
	else if($ActionMode=="delete")
		$CurrentLogic->DeleteDoc();      //회람지저장
 	else
		$CurrentLogic->MakeDoc();        //회람지작성폼

?>
