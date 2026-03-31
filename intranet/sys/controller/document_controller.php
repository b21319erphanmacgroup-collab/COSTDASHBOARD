<?
	/***************************************
	* 전자결재문서 작성
	* ------------------------------------
	* 2014-12-16 : 파일정리: JYJ KYH
	****************************************/
	session_start();
	if($_GET['memberID'] <> ""){
		$memberID = $_GET['memberID'];
	}
	
	if($_SESSION['memberID']==""){
		$_SESSION['memberID']=$memberID;
	}
	else{
		$memberID=$_SESSION['memberID'];
	}

	require('../util/LoginInfomation.php');
	$LoginIn = new LoginInfomation();
	$LoginIn->GetLoginStatusPop();


	include "../inc/dbcon.inc";
	include "../../../SmartyConfig.php";

	//if ($CompanyKind==""){$CompanyKind=$_COOKIE['CK_CompanyKind'];}
	if ($CompanyKind==""){$CompanyKind=$_SESSION['CK_CompanyKind'];}	//쿠키정보 세션으로 대체 250414 김진선

	if($_REQUEST['mobile']=="y"){
		//include "../model/document_logic_HANM_jmj.php";
		include "../model/document_logic_HANM.php";
	}else{
		if($_SESSION[memberID]=="M22014"){
			//include "../model/document_logic_HANM_khg.php";
			include "../model/document_logic_HANM.php";
		}
		elseif( ( $_SESSION[memberID]=="M20330a" or $_SESSION[memberID]=="B20334a" or $_SESSION[memberID]=="M22021a" )  ){
			//include "../model/document_logic_HANM_vacation.php";
			include "../model/document_logic_HANM_jmj.php";
		}
		elseif($_SESSION[memberID]=="M21420"){ 
			include "../model/document_logic_HANM.php";
		}
		else{
			include "../model/document_logic_HANM.php";
		}

	}




	require_once($SmartyClassPath);

	$smarty = new Smarty($smarty);
	$smarty->template_dir	=$SmartyClass_TemplateDir;
    $smarty->compile_dir	=$SmartyClass_CompileDir;
    $smarty->config_dir		=$SmartyClass_ConfigDir;
    $smarty->cache_dir		=$SmartyClass_CacheDir;

	$CurrentLogic=new DocumentLogic($smarty);

	if($ActionMode=="insert_page")				//전자결재 작성창 이동
		$CurrentLogic->InsertPage();
	else if($ActionMode=="insert")				//전자결재 작성data 저장(insert)
		$CurrentLogic->InsertAction();
	else if($ActionMode=="update_page" || $ActionMode=="delete_page")//전자결재 수정창 상신/재기안
		$CurrentLogic->UpdateReadPage();
	else if($ActionMode=="update")				//전자결재 수정data 저장(update)
		$CurrentLogic->UpdateAction();
	else if($ActionMode=="delete")				//전자결재 삭제
		$CurrentLogic->DeleteAction();
	else if($ActionMode=="Pass_check")			//전자결재 비밀번호 확인
		$CurrentLogic->Passcheck();
	else if($ActionMode=="parking_print")			//전자결재 비밀번호 확인
		$CurrentLogic->ParkingPrint();
	else if($ActionMode=="fnGetData")			//전자결재 비밀번호 확인
		$CurrentLogic->fnGetData();
	else if($ActionMode=="doc_print")			//발신공문출력
		$CurrentLogic->doc_print();
	else if($ActionMode=="doc_print_test")			//발신공문출력
		$CurrentLogic->doc_print_test();
	else if($ActionMode=="doc_date")			//발신공문발행일변경
		$CurrentLogic->doc_date();
	else if($ActionMode=="delete_file")			//전자결재 파일삭제
		$CurrentLogic->DeleteFile();
	else if($ActionMode=="CheckEditVacation")		//결재중인 연차사용일 변경이 존재하는지 체크.
		$CurrentLogic->CheckEditVacation();
	else if($ActionMode=="doc_stamp")		//수신문서 접수 도장
		$CurrentLogic->doc_stamp();
	else if($ActionMode=="intranet_confirm")		//ERP쪽에서 인트라넷 정보확인 250818
	    $CurrentLogic->intranet_confirm();
?>
