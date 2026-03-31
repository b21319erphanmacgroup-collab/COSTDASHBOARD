<?
	/***************************************
	* 업무협조전 작성
	* ------------------------------------
	* 2014-12-16 : 파일정리: KYH
	****************************************/
	/*
	session_start();
	extract($_REQUEST);
	if($_SESSION['SS_memberID']=="")
	{
		$_SESSION['SS_memberID']=$memberID;
	}else{
		$memberID=$_SESSION['SS_memberID'];
	}
	require('../util/LoginInfomation.php');
	$LoginIn = new LoginInfomation();
	$LoginIn->GetLoginStatusPop();
*/
	
	session_start();
	if($_SESSION['memberID']=="")
	{
		//return false;
		 echo "
			 <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
			  <script>
			   parent.document.location.href='/intranet/index.php?logoutFlag=2';
				alert('로그인후 사용하세요');
			  </script>
			 ";
		$_SESSION['memberID']=$memberID;
		
	}else{
		$memberID=$_SESSION['memberID'];
	}
	require('../util/LoginInfomation.php');
	$LoginIn = new LoginInfomation();
	$LoginIn->GetLoginStatus();
	

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

	if($CompanyKind=="JANG")
			include "../model/cooperation_logic_JANG.php";
	else if ($CompanyKind=="PILE")
			include "../model/cooperation_logic_PILE.php";
	else if ($CompanyKind=="HANM")
			include "../model/cooperation_logic_HANM.php";
	else if ($CompanyKind=="BARO")
			include "../model/cooperation_logic_HANM.php";

	require_once($SmartyClassPath);

	$smarty = new Smarty($smarty);
	$smarty->template_dir	=$SmartyClass_TemplateDir;
    $smarty->compile_dir	=$SmartyClass_CompileDir;
    $smarty->config_dir		=$SmartyClass_ConfigDir;
    $smarty->cache_dir		=$SmartyClass_CacheDir;

	$CurrentLogic=new CooperationLogic($smarty);


	if($ActionMode=="list" || $ActionMode=="")	//업무협조전 리스트
		$CurrentLogic->ListPage();
	else if($ActionMode=="insert_page")			//업무협조전 작성창 이동
		$CurrentLogic->InsertPage();
	else if($ActionMode=="insert")				//업무협조전 작성data 저장(insert)
		$CurrentLogic->InsertAction();
	else if($ActionMode=="update_page")         //업무협조전 수정창
		$CurrentLogic->UpdateReadPage();
	else if($ActionMode=="update")				//업무협조전 수정data 저장(update)
		$CurrentLogic->UpdateAction();
	else if($ActionMode=="delete")				//업무협조전 삭제
		$CurrentLogic->DeleteAction();
	else if($ActionMode=="MemberList")				//업무협조전 부서담당자 리스트
		$CurrentLogic->MemberList();
	else if($ActionMode=="MemberChangeList")		//업무협조전 부서담당자 변경팝업창
		$CurrentLogic->MemberChangeList();
	else if($ActionMode=="MemberChange")		//업무협조전 부서담당자 변경
		$CurrentLogic->MemberChange();
	else if($ActionMode=="accept")		//업무협조전 접수
		$CurrentLogic->AcceptPage();

?>
