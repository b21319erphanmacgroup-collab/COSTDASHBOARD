<?
	/***************************************
	* 수주 및 매출, 수금현황
	* ------------------------------------
	* 2014-01-14 : 파일정리: JYJ
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
	/*
	$LoginIn = new LoginInfomation();
	$LoginIn->GetLoginStatus();
	$memberID = $_SESSION['memberID'];
	*/
	include "../../sys/inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../../sys/model/business_result_logic_work.php";

	require_once($SmartyClassPath);

	$smarty = new Smarty($smarty);
	$smarty->template_dir	= $SmartyClass_TemplateDir;
	$smarty->compile_dir	= $SmartyClass_CompileDir;
	$smarty->config_dir		= $SmartyClass_ConfigDir;
	$smarty->cache_dir		= $SmartyClass_CacheDir;
	
$CurrentLogic=new BusinessResult($smarty);
	
	if($ActionMode=="contract_report")//수주,매출,수금현황
		$CurrentLogic->ContractReportLogic();
	
	else if($ActionMode=="contract_report2")//수주,매출,수금현황TEST
		$CurrentLogic->ContractReportLogic2();	
	
	
	else if($ActionMode=="sales_report")//수주,매출,수금현황
		$CurrentLogic->SalesReportLogic();
	else if($ActionMode=="collection_report")//수주,매출,수금현황
		$CurrentLogic->CollectionReportLogic();
	else if($ActionMode=="Noncontract_view")//미계약 수주현황 보기
		$CurrentLogic->NoncontractViewLogic();
	else if($ActionMode=="Noncontract_input")//미계약 수주현황 입력
		$CurrentLogic->NoncontractInputLogic();
	else if($ActionMode=="Noncontract_Action")//미계약 수주현황 저장/수정/삭제
		$CurrentLogic->NoncontractActionLogic();
	else if($ActionMode=="Consalegoal_input")//수주/매출 목표 입력창
		$CurrentLogic->ConsaleGoalInputLogic();
	else if($ActionMode=="Consalegoal_update")//수주/매출 목표 저장
		$CurrentLogic->ConsaleGoalUpdateLogic();
	else
		$CurrentLogic->BusinessResultList();   //출금전표 list 이동

?>