<?
	/***************************************
	* 조직도
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
	//$LoginIn->GetLoginStatus();
	$LoginIn->GetLoginSessionStatus();
	

		
	include "../../sys/inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../../sys/model/organization_chart_logic.php";

	require_once($SmartyClassPath);

	$smarty = new Smarty($smarty);
	$smarty->template_dir	= $SmartyClass_TemplateDir;
	$smarty->compile_dir	= $SmartyClass_CompileDir;
	$smarty->config_dir		= $SmartyClass_ConfigDir;
	$smarty->cache_dir		= $SmartyClass_CacheDir;
	
	$CurrentLogic = new OrganizationChart();

	if($ActionMode=="chart_graph")
		$CurrentLogic->OrganizationChartGraph();    //조직도 메인화면 (조직도 인원 구성 그래프 페이지)
	elseif($ActionMode=="org_list")
		$CurrentLogic->OrganizationChartList2();    //팀별 구성원 정보 페이지2
	else
		$CurrentLogic->OrganizationChartList();       //팀별 구성원 정보 페이지
	

?>
