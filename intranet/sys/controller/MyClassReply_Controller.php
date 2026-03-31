<? 
	$ActionMode = $_REQUEST['ActionMode'];
	$Company_Kind = $_REQUEST['Company_Kind'];
	
		
	include "../inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../model/MyClassReply_Logic.php";
	require_once($SmartyClassPath);
	
	$smarty = new Smarty($smarty);
	$smarty->template_dir = $SmartyClass_TemplateDir;
	$smarty->compile_dir = $SmartyClass_CompileDir;
	$smarty->config_dir = $SmartyClass_ConfigDir;
	$smarty->cache_dir = $SmartyClass_CacheDir;
	
	$CurrentLogic = new MyclassLogic($smarty);
	
	if ($ActionMode == "MyClass_Reply") {
		$CurrentLogic->MyClass_Reply();
	} else if ($ActionMode == "MyClass_View") {
		$CurrentLogic->MyClass_View();
	} else {
		$CurrentLogic->MyClass_Reply();
	}
?>

