<?
	session_start();

	/*
	require('../../util/GetUserInfo.php');
	require('../../util/LoginInfomation.php');
	*/

	//print_r($_SESSION);
	if(!$_SESSION['auth_planning']){	//권한 체크
		//header("Location:/intranet");
		//return false;
	}

	include "../../../SmartyConfig.php";
	//include "../model/Common/AccessModeCheck.php";

	require_once($SmartyClassPath);

	extract($_REQUEST);
	$ActionMode=$_REQUEST['ActionMode'];
	//if($COMPANY == ''){ $COMPANY = 'SAMAN'; }


	//include "../../model/Person/PersonInsaReport_logic.php";
	if($test == ''){
		include "../model/PersonInsaReport_logic.php";
	}else{
		include "../model/PersonInsaReport_logic_".$test.".php";
	}

	$smarty = new Smarty($smarty);
	$smarty->template_dir	=$SmartyClass_TemplateDir;
    $smarty->compile_dir	=$SmartyClass_CompileDir;
    $smarty->config_dir		=$SmartyClass_ConfigDir;
    $smarty->cache_dir		=$SmartyClass_CacheDir;

	//=======================================================
	// 로그인 상태를 점검한다
	//=======================================================
	/*
	$AuthCheck=new AccessModeCheckLogic($smarty);
	if(!$AuthCheck->IsValidSession()) return;
	*/


	$smarty->assign('test',$test);
	$smarty->assign('Controller',"PersonInsaReport_Controller.php");

	$CurrentLogic=new PersonInsaReportLogic($smarty, $COMPANY);

	if($ActionMode=="REPORT_01")
		$CurrentLogic->REPORT_01();			// 인사정보
	else if($ActionMode=="REPORT_02")
		$CurrentLogic->REPORT_02();			// 근태분석
	else if($ActionMode=="REPORT_03")
		$CurrentLogic->REPORT_03();			// 근태분석3
	else if($ActionMode=="REPORT_04")
		$CurrentLogic->REPORT_04();			// 년도별 전체 근무 목록
	else if($ActionMode=="REPORT_05")
		$CurrentLogic->REPORT_05();			// 직급별 근무분석
	else if($ActionMode=="REPORT_05_1")
		$CurrentLogic->REPORT_05_1();			// 직급별 근무분석_수정분
	else if($ActionMode=="REPORT_06")
		$CurrentLogic->REPORT_06();			// 개인-평균 비교 그래프
	else if($ActionMode=="REPORT_07")
		$CurrentLogic->REPORT_07();			// 데이터 확인 화면
	else if($ActionMode=="REPORT_CHECK")
		$CurrentLogic->REPORT_CHECK();			// oracle 접속 체크
	else
		$CurrentLogic->REPORT_00();			// 사이트맵

?>