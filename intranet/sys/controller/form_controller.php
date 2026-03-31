<?php
	/***************************************
	* 사내양식
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
	$LoginIn->GetLoginStatus();
	
	include "../inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../model/form_logic.php";
	require_once($SmartyClassPath);

    $smarty = new Smarty($smarty);
	$smarty->template_dir	=$SmartyClass_TemplateDir;
    $smarty->compile_dir	=$SmartyClass_CompileDir;
    $smarty->config_dir		=$SmartyClass_ConfigDir;
    $smarty->cache_dir		=$SmartyClass_CacheDir;

	$CurrentLogic=new form_logic($smarty);
	
	if($ActionMode=="insert_page")                                          //사내양식 등록 페이지 이동
		$CurrentLogic->InsertPage();
	else if($ActionMode=="insert")                                          //사내양식 저장(insert)
		$CurrentLogic->InsertAction();
	else if($ActionMode=="update_page" or $ActionMode=="delete_page")		//사내양식 수정 페이지 이동
		$CurrentLogic->UpdateReadPage();
	else if($ActionMode=="update")                                          //사내양식 저장(update)
		$CurrentLogic->UpdateAction();
	else if($ActionMode=="delete_page")                                     //사내양식 자료삭제 페이지 이동
		$CurrentLogic->DeletePage();
	else if($ActionMode=="delete")                                          //사내양식 자료삭제
		$CurrentLogic->DeleteAction();
	else if($ActionMode=="find_page")                                       //사내양식 검색 페이지 이동
		$CurrentLogic->View();
	else
		$CurrentLogic->View();                                              //사내양식 list 페이지 이동

?>
