<?php

	/***************************************
	* 공지사항
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
	include "../model/NoticeAddLogic.php";

	require_once($SmartyClassPath);

    $smarty = new Smarty($smarty);
	$smarty->template_dir	=$SmartyClass_TemplateDir;
    $smarty->compile_dir	=$SmartyClass_CompileDir;
    $smarty->config_dir		=$SmartyClass_ConfigDir;
    $smarty->cache_dir		=$SmartyClass_CacheDir;

	$CurrentLogic=new NoticeAddLogic($smarty);
	extract($_REQUEST);

	if($ActionMode=="insert_page")         //공지사항 입력 페이지
		$CurrentLogic->InsertPage();
	else if($ActionMode=="insert"){		     //공지사항 저장 (insert)
		 
		$CurrentLogic->InsertAction_file();
		
		//$CurrentLogic->InsertAction();
	}else if($ActionMode=="update_page" or $ActionMode=="delete_page" or $ActionMode=="popup_page")	//공지사항 수정 / 팝업 페이지
		$CurrentLogic->UpdateReadPage();
	else if($ActionMode=="update")		 //공지사항 저장 (update)
		//$CurrentLogic->UpdateAction();
		$CurrentLogic->UpdateAction_file();
	else if($ActionMode=="delete_page")	 //공지사항 삭제 비밀번호 입력페이지
		$CurrentLogic->DeletePage();
	else if($ActionMode=="delete")			 //공지사항 삭제 (delete)
		$CurrentLogic->DeleteAction();
	else if($ActionMode=="updateread")	 //공지사항 읽기 페이지 (popup)
		$CurrentLogic->UpdateRead();
	else if($ActionMode=="find_page")		//공지사항 검색
		$CurrentLogic->View();
	else if($ActionMode=="read_list")		//공지사항 확인자
		$CurrentLogic->Readlist();
	else if($ActionMode=="temp_page")		//공지사항 미리보기
		$CurrentLogic->temp_page();
	else if($ActionMode=="VIDEO_PLAYER")	
		$CurrentLogic->VIDEO_PLAYER();
	else if($ActionMode=="AUTOCOMPLETE")	
		$CurrentLogic->AUTOCOMPLETE();
	else if($ActionMode=="AUTOCOMPLETE_DATA")	
		$CurrentLogic->AUTOCOMPLETE_DATA();//autocomplete_data
	else
		$CurrentLogic->View();


?>
