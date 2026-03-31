<?php

	/***************************************
	* 부서장 팝업 컨트롤러
	* ------------------------------------
	* 2017-05-25 : 파일정리: JMJ
	****************************************/
	include "../inc/dbcon.inc";
	include "../../../SmartyConfig.php";
	include "../model/NotifyMessageLogic.php";

	require_once($SmartyClassPath);

    $smarty = new Smarty($smarty);
	$smarty->template_dir	=$SmartyClass_TemplateDir;
    $smarty->compile_dir	=$SmartyClass_CompileDir;
    $smarty->config_dir		=$SmartyClass_ConfigDir;
    $smarty->cache_dir		=$SmartyClass_CacheDir;

	$CurrentLogic=new NotifyMessageLogic($smarty);


	if($ActionMode=="sanction_auth"){		//부서장인지 체크
		$CurrentLogic->SanctionAuth();
	}elseif($ActionMode=="UserCheck"){	//사용자 체크
		$CurrentLogic->UserCheck();
	}elseif($ActionMode=="sanction_list_new"){	//결재할 문서 체크
		$CurrentLogic->SanctionList_new();
	}elseif($ActionMode=="sanction_count"){	//결재할 문서 체크
		$CurrentLogic->SanctionCount();
	}

?>
