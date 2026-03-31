<?	
	
	/***************************************
	* 기본 결재 수신인 설정
	* ------------------------------------
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
	$LoginIn->GetLoginStatusPop();


	include "../inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../model/approval_setting_logic.php";
	require_once($SmartyClassPath);
    
	$smarty = new Smarty($smarty);
	$smarty->template_dir	=$SmartyClass_TemplateDir;
    $smarty->compile_dir	=$SmartyClass_CompileDir;
    $smarty->config_dir		=$SmartyClass_ConfigDir;
    $smarty->cache_dir		=$SmartyClass_CacheDir;


	$CurrentLogic=new ApprovalSettingLogic($smarty);

	if($ActionMode=="insert_page")	//결재선설정 입력 페이지 이동
		$CurrentLogic->InsertPage();
	else if($ActionMode=="insert")	//결재선설정 저장(insert/update)
		$CurrentLogic->InsertAction();
	else if($ActionMode=="InsertDocPage")	//발신공문결재선설정
		$CurrentLogic->InsertDocPage();
	else if($ActionMode=="InsertDocAction")	//발신공문결재선설정저장
		$CurrentLogic->InsertDocAction();


?>
