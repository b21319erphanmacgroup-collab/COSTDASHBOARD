<? 
	$ActionMode = $_REQUEST['ActionMode'];
	$Company_Kind = $_REQUEST['Company_Kind'];
	
	require('../util/LoginInfomation.php');
	$LoginIn = new LoginInfomation();
	$LoginIn->GetLoginStatus();
	
	include "../inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../model/Study_Logic2.php";
	require_once($SmartyClassPath);
	
	$smarty = new Smarty($smarty);
	$smarty->template_dir = $SmartyClass_TemplateDir;
	$smarty->compile_dir = $SmartyClass_CompileDir;
	$smarty->config_dir = $SmartyClass_ConfigDir;
	$smarty->cache_dir = $SmartyClass_CacheDir;
	
	$CurrentLogic = new StudyLogic($smarty);
	
	if ($ActionMode == "MyClass_Mobile") {
		$CurrentLogic->MyClass_Mobile();
	}
?>
