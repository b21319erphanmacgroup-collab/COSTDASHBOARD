<?php
	/***************************************
	* 지적재산권
	* ------------------------------------
	* 2014-12-16 : 파일정리: JYJ
	****************************************/ 
	header('Access-Control-allow-Origin:*');	
	session_start();
	
	extract($_REQUEST);
	if($_SESSION['memberID']==""){$_SESSION['memberID']=$memberID;}
	else{$memberID=$_SESSION['memberID'];}
	
	if($companyname!="HANMAC" && $companyname!=""){$_REQUEST['companyname']=$companyname;}
	else{
		$_REQUEST['companyname']=$companyname;
		require('../util/LoginInfomation.php');
		$LoginIn = new LoginInfomation();
		$LoginIn->GetLoginStatus();
	}
	
	include "../inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../model/intellectual_property_logic.php";
	
	require_once($SmartyClassPath);
    
	$smarty = new Smarty($smarty);
	$smarty->template_dir	=$SmartyClass_TemplateDir;
    $smarty->compile_dir	=$SmartyClass_CompileDir;
    $smarty->config_dir		=$SmartyClass_ConfigDir;
    $smarty->cache_dir		=$SmartyClass_CacheDir;
    
    $smarty->assign('Controller',"intellectual_property_controller.php");

	$CurrentLogic=new IntellectualPropertyLogic($smarty);
	
	
	if($ActionMode=="insert_page")                                      //지적재산권 입력창
		$CurrentLogic->InsertPage();
	else if($ActionMode=="insert")									    //지적재산권 저장(insert)
		$CurrentLogic->InsertAction();
	else if($ActionMode=="update_page" or $ActionMode=="read_page")     //지적재산권 수정/읽기 페이지 (한맥)
		$CurrentLogic->UpdateReadPage();
	else if($ActionMode=="update")										//지적재산권 저장 한맥(update)
		$CurrentLogic->UpdateAction();
	else if($ActionMode=="delete")										//지적재산권 삭제(delete)
		$CurrentLogic->DeleteAction();
	else if($ActionMode=="find_page")									//지적재산권 검색
		$CurrentLogic->View();
	else if($ActionMode=="paymentsave")								//지적재산권 납입정보 저장(insert)
		$CurrentLogic->PaymentSave();
	else if($ActionMode=="paymentdelete")								//지적재산권 납입정보 삭제(delete)
		$CurrentLogic->PaymentDelete();
	else if($ActionMode=="popup_page")									//사람검색
		$CurrentLogic->Popuppage();
	else if($ActionMode=="PaymentPage")									//납입정보
		$CurrentLogic->PaymentPage();
	else if($ActionMode=="detail_view01")									//상세사항_양식1
		$CurrentLogic->detail_view01();
	else if($ActionMode=="detail_view02")									//상세사항_양식2
		$CurrentLogic->detail_view02();
	else											                     //지적재산권 LIST
		$CurrentLogic->View();

?>
