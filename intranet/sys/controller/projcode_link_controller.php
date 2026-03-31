<?php

	/***************************************
	* 프로젝트코드 링크
	* ------------------------------------
	* 2016-08-30 : sjh
	****************************************/ 

	session_start();
	include "../../../SmartyConfig.php";
	include "../model/Projcode_link_logic.php";

	require_once($SmartyClassPath);
    
	$smarty = new Smarty($smarty);
	$smarty->template_dir	=$SmartyClass_TemplateDir;
    $smarty->compile_dir	=$SmartyClass_CompileDir;
    $smarty->config_dir		=$SmartyClass_ConfigDir;
    $smarty->cache_dir		=$SmartyClass_CacheDir;

	$ActionMode=$_REQUEST['ActionMode'];
	$CurrentLogic=new ProjcodeLinkLogic($smarty);
	$CurrentLogic->LinkProcess();
?>