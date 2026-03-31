<?php

	/***************************************
	* 사원 검색 컨트롤러
	* ------------------------------------
	* 2017-06-05 : 파일정리: JMJ
	****************************************/
	include "../inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../model/familycontactLogic.php";

	require_once($SmartyClassPath);

    $smarty = new Smarty($smarty);
	$smarty->template_dir	=$SmartyClass_TemplateDir;
    $smarty->compile_dir	=$SmartyClass_CompileDir;
    $smarty->config_dir		=$SmartyClass_ConfigDir;
    $smarty->cache_dir		=$SmartyClass_CacheDir;

	$CurrentLogic=new familycontactLogic($smarty);

	$CurrentLogic->MemberList();


?>
