<?
	require_once realpath(dirname(__FILE__) . "/../util/dashBoard/session_init.php");
	require_once realpath(dirname(__FILE__) . "/../util/dashBoard/SessionAuth.php");
	$auth = new SessionAuth();
	
	$ActionMode = isset($_REQUEST["ActionMode"]) ? $_REQUEST["ActionMode"] : "login";
	$publicActionMode = array(
		'login',
		'batch'
	);
	
	if (!in_array($ActionMode, $publicActionMode)) {
		$auth->requireLogin();
	}
	
	include_once realpath(dirname(__FILE__) . "/../../../SmartyConfig.php");
	include_once realpath(dirname(__FILE__) . "/../../sys/model/costDashBoard_logic.php");
	require_once($SmartyClassPath);

	$smarty = new Smarty($smarty);
	$smarty->template_dir	= $SmartyClass_TemplateDir;
	$smarty->compile_dir	= $SmartyClass_CompileDir;
	$smarty->config_dir		= $SmartyClass_ConfigDir;
	$smarty->cache_dir		= $SmartyClass_CacheDir;
		
	$GoLogic = new CostDashBoard($smarty);
	if ($ActionMode=="login") {
		$GoLogic->login();
	} else if ($ActionMode=="main") {	
		$GoLogic->main();
	} else if ($ActionMode=="batch") {
		$GoLogic->batch();
	} 
?>
