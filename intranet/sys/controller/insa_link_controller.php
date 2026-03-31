<?php

	/***************************************

	* ------------------------------------
	* 2016-04-06 :
	****************************************/

	session_start();
	include "../../../SmartyConfig.php";
	include "../model/Insa_link_logic.php";

	require_once($SmartyClassPath);

	$smarty = new Smarty($smarty);
	$smarty->template_dir	=$SmartyClass_TemplateDir;
    $smarty->compile_dir	=$SmartyClass_CompileDir;
    $smarty->config_dir		=$SmartyClass_ConfigDir;
    $smarty->cache_dir		=$SmartyClass_CacheDir;

	$ActionMode=$_REQUEST['ActionMode'];
	/*
	$CurrentLogic=new InsaLinkLogic($smarty);
	if($ActionMode == 'test'){
		$CurrentLogic->LinkProcess_test();
	}else{
		$CurrentLogic->LinkProcess();
	}
	*/


	$CurrentLogic=new InsaLinkLogic($smarty);
	if($ActionMode == 'test'){
		$CurrentLogic->LinkProcess_test();
	}else{
		$CurrentLogic->LinkProcess_test();
	}

?>