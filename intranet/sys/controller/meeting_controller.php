<?
	/***************************************
	* 회의록 
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
	include "../model/meeting_logic.php";
	require_once($SmartyClassPath);

    $smarty = new Smarty($smarty);
	$smarty->template_dir	=$SmartyClass_TemplateDir;
    $smarty->compile_dir	=$SmartyClass_CompileDir;
    $smarty->config_dir		=$SmartyClass_ConfigDir;
    $smarty->cache_dir		=$SmartyClass_CacheDir;
	
	$CurrentLogic=new PDSLogic($smarty);
	
	if($ActionMode=="insert_page")                                     //회의록 입력
		$CurrentLogic->InsertPage();
	else if($ActionMode=="insert")                                     //회의록 저장(insert)
		$CurrentLogic->InsertAction();
	else if($ActionMode=="update_page" or $ActionMode=="delete_page")  //회의록 읽기/삭제 페이지
		$CurrentLogic->UpdateReadPage();
	else if($ActionMode=="update")                                     //회의록 저장(update)
		$CurrentLogic->UpdateAction();
	else if($ActionMode=="delete")                                     //회의록 삭제(delete)
		$CurrentLogic->DeleteAction();
	else if($ActionMode=="find_page")                                  //회의록 검색
		$CurrentLogic->View();											//회의록 LIST	
	else
		$CurrentLogic->View();
?>
