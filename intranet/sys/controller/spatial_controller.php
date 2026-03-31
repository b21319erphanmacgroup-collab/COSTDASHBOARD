<?	

	/***************************************
	* 기술자료,경제특강,기획특강 
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

	//include "../inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../model/spatial_logic.php";
	require_once($SmartyClassPath);
    
	$smarty = new Smarty($smarty);
	$smarty->template_dir	=$SmartyClass_TemplateDir;
    $smarty->compile_dir	=$SmartyClass_CompileDir;
    $smarty->config_dir		=$SmartyClass_ConfigDir;
    $smarty->cache_dir		=$SmartyClass_CacheDir;

	$CurrentLogic=new SpatialLogic($smarty);

	if($ActionMode=="REMAIN_DW_COUNT"){
		$CurrentLogic->REMAIN_DW_COUNT();
	}else if($ActionMode=="STATUS_CHECK"){
		$CurrentLogic->STATUS_CHECK();
	}else if($ActionMode=="STATUS_PING"){
		$CurrentLogic->STATUS_PING();
	}else if($ActionMode=="CH_STTUS"){
		$CurrentLogic->SPATIAL_CH_STTUS_DW();
	}else if($ActionMode=="CHECK_SKIN_TYPE"){
		$CurrentLogic->SPATIAL_CHECK_SKIN_TYPE();
		
		
	}else{
		$CurrentLogic->View();
	}
?>
