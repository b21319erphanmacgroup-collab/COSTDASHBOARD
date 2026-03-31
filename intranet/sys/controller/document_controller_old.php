<?
	/***************************************
	* 전자결재문서 작성
	* ------------------------------------
	* 2014-12-16 : 파일정리: JYJ KYH
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

	require('../util/LoginInfomation.php');
	$LoginIn = new LoginInfomation();
	$LoginIn->GetLoginStatusPop();


	include "../inc/dbcon.inc";
	include "../../../SmartyConfig.php";

	if ($CompanyKind==""){$CompanyKind=$_COOKIE['CK_CompanyKind'];}

	if ($CompanyKind=="")
	{
		$sql=" SELECT	* FROM systemconfig_tbl WHERE	SysKey='CompanyKind'";
			$result = mysql_query($sql, $db);
			$re_num = mysql_num_rows($result);
			$CompanyKind	= mysql_result($result,0,"Code");//회사코드
	}

	include "../model/document_logic_old.php";

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
?>
