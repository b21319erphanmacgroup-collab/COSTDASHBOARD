<?
	include "../../../SmartyConfig.php";
	include "../model/PdfLogic.php";

	require_once($SmartyClassPath);

	$smarty = new Smarty($smarty);
	$smarty->template_dir	=$SmartyClass_TemplateDir;
    $smarty->compile_dir	=$SmartyClass_CompileDir;
    $smarty->config_dir		=$SmartyClass_ConfigDir;
    $smarty->cache_dir		=$SmartyClass_CacheDir;

	$CurrentLogic=new PdfLogic($smarty);

	if($ActionMode=="MakePDF")
		$CurrentLogic->MakePDF();
	elseif($ActionMode=="test")
		$CurrentLogic->test();
?>