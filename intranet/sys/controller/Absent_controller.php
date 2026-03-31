<?php

	session_start();
	include "../../sys/inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../model/Absent_logic.php";
	require_once($SmartyClassPath);
    
	$smarty = new Smarty($smarty);
	$smarty->template_dir	=$SmartyClass_TemplateDir;
    $smarty->compile_dir	=$SmartyClass_CompileDir;
    $smarty->config_dir		=$SmartyClass_ConfigDir;
    $smarty->cache_dir		=$SmartyClass_CacheDir;

	$CurrentLogic=new AbsentController($smarty);

	if($ActionMode=="save")				
		$CurrentLogic->SetAbsent();
	else if($ActionMode=="save2")				
		$CurrentLogic->SetAbsent2();
	else if($ActionMode=="list")				
		$CurrentLogic->ShowList();
	else if($ActionMode=="list2")				
		$CurrentLogic->ShowList2();
	else if($ActionMode=="listcom")				
		$CurrentLogic->ShowListCom();
	else 
		$CurrentLogic->View();
?>