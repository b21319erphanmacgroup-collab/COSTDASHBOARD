<?php	
	/***************************************
	* 지적재산권
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
	$LoginIn->GetLoginStatus();

	include "../inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../model/patent_logic.php";
	require_once($SmartyClassPath);
    
	$smarty = new Smarty($smarty);
	$smarty->template_dir	=$SmartyClass_TemplateDir;
    $smarty->compile_dir	=$SmartyClass_CompileDir;
    $smarty->config_dir		=$SmartyClass_ConfigDir;
    $smarty->cache_dir		=$SmartyClass_CacheDir;

	$CurrentLogic=new PatentLogic($smarty);
	
	if($ActionMode=="insert_page")                                      //지적재산권 입력창
		$CurrentLogic->InsertPage();
	if($ActionMode=="HMinsert_page")                                      //지적재산권 입력창
		$CurrentLogic->HMInsertPage();
	else if($ActionMode=="insert")									    //지적재산권 저장(insert)
		$CurrentLogic->InsertAction();
	else if($ActionMode=="HMinsert")									    //지적재산권 저장(insert)
		$CurrentLogic->HMInsertAction();
	else if($ActionMode=="update_page" or $ActionMode=="read_page")     //지적재산권 수정/읽기 페이지
		$CurrentLogic->UpdateReadPage();
	else if($ActionMode=="HMupdate_page" or $ActionMode=="HMread_page")     //지적재산권 수정/읽기 페이지 (한맥)
		$CurrentLogic->HMUpdateReadPage();
	else if($ActionMode=="update")										//지적재산권 저장(update)
		$CurrentLogic->UpdateAction();
	else if($ActionMode=="HM_update")										//지적재산권 저장 한맥(update)
		$CurrentLogic->HMUpdateAction();
	else if($ActionMode=="delete")										//지적재산권 삭제(delete)
		$CurrentLogic->DeleteAction();
	else if($ActionMode=="find_page")									//지적재산권 검색
		$CurrentLogic->ViewHM();
	else											                     //지적재산권 LIST
		$CurrentLogic->ViewHM();

?>
