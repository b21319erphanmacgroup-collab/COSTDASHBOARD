<?php	
	/***************************************
	* 도서관 프로그램
	* ------------------------------------
	* 2015-07-10 : 파일정리: JYJ
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
	//$LoginIn->GetLoginStatus();

	include "../inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../model/library_logic.php";
	require_once($SmartyClassPath);
    
	$smarty = new Smarty($smarty);
	$smarty->template_dir	=$SmartyClass_TemplateDir;
    $smarty->compile_dir	=$SmartyClass_CompileDir;
    $smarty->config_dir		=$SmartyClass_ConfigDir;
    $smarty->cache_dir		=$SmartyClass_CacheDir;

	$CurrentLogic=new LibraryLogic($smarty);
	
	if($ActionMode=="insert_page")                                      //지적재산권 입력창
		$CurrentLogic->InsertPage();
	if($ActionMode=="HMinsert_page")                                      //지적재산권 입력창
		$CurrentLogic->HMInsertPage();
	else if($ActionMode=="HMinsert")									    //지적재산권 저장(insert)
		$CurrentLogic->HMInsertAction();
	else if($ActionMode=="HMupdate_page" or $ActionMode=="HMread_page")     //지적재산권 수정/읽기 페이지 (한맥)
		$CurrentLogic->HMUpdateReadPage();
	else if($ActionMode=="HM_update")										//지적재산권 저장 한맥(update)
		$CurrentLogic->HMUpdateAction();
	else if($ActionMode=="delete")										//지적재산권 삭제(delete)
		$CurrentLogic->DeleteAction();
	else if($ActionMode=="HMrent_page")									//지적재산권 검색
		$CurrentLogic->HMrent_page();
	else if($ActionMode=="HMRentInsert" or $ActionMode=="HMRentUpdate")									//지적재산권 검색
		$CurrentLogic->HMRentInsertUpdate();
	else if($ActionMode=="HMreturn_page")									//도서반납확인
		$CurrentLogic->HMreturn_page();
	else if($ActionMode=="HMApprovalCheck")									//도서대여승인
		$CurrentLogic->HMApprovalCheck();
	else if($ActionMode=="HMReturnCheck")									//도서반납확인
		$CurrentLogic->HMReturnCheck();
	else if($ActionMode=="HMApprovalCheck2")									//도서반납확인2
		$CurrentLogic->HMApprovalCheck2();
	else if($ActionMode=="HMReturnCheck2")									//도서반납확인2
		$CurrentLogic->HMReturnCheck2();
	else if($ActionMode=="HMapplication_list")									//도서대여 현황 list
		$CurrentLogic->HMapplication_list();
	else if($ActionMode=="status_list")									
		$CurrentLogic->HMstatus_list();
	else											                     
		$CurrentLogic->ViewHM();

?>
