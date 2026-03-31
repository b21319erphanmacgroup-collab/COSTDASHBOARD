<?
	/***************************************
	* 사장님 페이지
	* ------------------------------------
	* 2022-12-15 : 정명준
	****************************************/
	include "../model/SJN_logic.php";

	require_once($SmartyClassPath);

	$smarty = new Smarty($smarty);
	$smarty->template_dir	=$SmartyClass_TemplateDir;
    $smarty->compile_dir	=$SmartyClass_CompileDir;
    $smarty->config_dir		=$SmartyClass_ConfigDir;
    $smarty->cache_dir		=$SmartyClass_CacheDir;

	$CurrentLogic=new SJNLogic($smarty);

	if($ActionMode=="meeting")				// 회의실
		$CurrentLogic->meeting();
	else if($ActionMode=="info")				// 정보
		$CurrentLogic->info();
?>
