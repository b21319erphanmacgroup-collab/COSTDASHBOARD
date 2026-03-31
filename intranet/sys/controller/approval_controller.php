<?php
	/***************************************
	* 전자결재 리스트
	* ------------------------------------
	* 2014-12-   :
	* 2014-12-16 : 파일정리: KYH
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

	/* ------------------------------------- */
	include "../inc/dbcon.inc";
	include "../../../SmartyConfig.php";

	//if  ($CompanyKind==""){$CompanyKind=$_COOKIE['CK_CompanyKind'];}
	if  ($CompanyKind==""){$CompanyKind=$_SESSION['CK_CompanyKind'];}	//쿠키정보 세션으로 대체 250626 김진선

	if ($CompanyKind=="")
	{
		$sql=" SELECT	* FROM systemconfig_tbl WHERE	SysKey='CompanyKind'";
			$result = mysql_query($sql, $db);
			$re_num = mysql_num_rows($result);
			$CompanyKind	= mysql_result($result,0,"Code");//회사코드
	}

	if( $_REQUEST['mobile'] == 'y' ){
			//include "../model/approval_logic_HANM_jmj.php";
			include "../model/approval_logic_HANM.php";
	}elseif($memberID=="M20330")
			//include "../model/approval_logic_HANM_jmj.php";
			include "../model/approval_logic_HANM.php";
	elseif($CompanyKind=="JANG")
			include "../model/approval_logic_JANG.php";
	else if ($CompanyKind=="PILE")
			include "../model/approval_logic_PILE.php";
	else if ($CompanyKind=="HANM")
			include "../model/approval_logic_HANM.php";
	else
			include "../model/approval_logic_HANM.php";


	require_once($SmartyClassPath);
	/* ------------------------------------- */
	$smarty = new Smarty($smarty);
	$smarty->template_dir	=$SmartyClass_TemplateDir;
	$smarty->compile_dir	=$SmartyClass_CompileDir;
	$smarty->config_dir		=$SmartyClass_ConfigDir;
	$smarty->cache_dir		=$SmartyClass_CacheDir;
	/* ------------------------------------- */
	$CurrentLogic=new ApprovalLogic($smarty);

	/* ------------------------------------- */
	if($ActionMode=="delete"){				//전자결재 삭제
		$CurrentLogic->DeleteAction();
	}else if($ActionMode=="Admin_211007")	{		//결재자 전체보기
		$CurrentLogic->AdminListView();
	}else if($ActionMode=="Admin")	{		//결재자 전체보기
		$CurrentLogic->Admin();
	}else if($ActionMode=="baby_vacation")	{		//출산휴가 신청서 목록
		$CurrentLogic->baby_vacation();
	}else{
		if($Category=="MyListView"){
			$CurrentLogic->MyListView();    //전자결재 양식별 LIST
		}else{
			$CurrentLogic->View();          //전자결재 결재현황별 LIST
		}
	}
	/* ------------------------------------- */
?>
