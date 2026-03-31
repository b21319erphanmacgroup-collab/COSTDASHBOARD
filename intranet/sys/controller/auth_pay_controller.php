<?	
	/***************************************
	* 권한설정
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
	include "../model/auth_pay_logic.php";
	require_once($SmartyClassPath);
    
	$smarty = new Smarty($smarty);
	$smarty->template_dir	=$SmartyClass_TemplateDir;
    $smarty->compile_dir	=$SmartyClass_CompileDir;
    $smarty->config_dir		=$SmartyClass_ConfigDir;
    $smarty->cache_dir		=$SmartyClass_CacheDir;
	
	$CurrentLogic=new AuthPayLogic($smarty);

	if($ActionMode=="update_page")		//개인별 권한설정보기
		$CurrentLogic->UpdateReadPage(); 
	else if($ActionMode=="update")		//개인열 권한설정하기
		$CurrentLogic->UpdateAction();
	else if($ActionMode=="get_token")	//토큰 가져오기
		$CurrentLogic->getToken();
	else if($ActionMode=="get_token_api")	//토큰 가져오기(api용)
		$CurrentLogic->getTokenApi();
	else								//전체보기
		$CurrentLogic->View();	
?>
